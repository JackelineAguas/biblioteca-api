<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "name" => 'required',
            "email" => 'required|email|unique:users',
            "password" => 'required|confirmed'
        ]);

        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                //Validate request data
                $request->validate([
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|confirmed'
                ]);
                //Set data
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->save();
                DB::commit();
                return $this->getResponse201('user account', 'created', $user);
            } catch (Exception $e) {
                DB::rollBack();
                return $this->getResponse500([$e->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!$validator->fails()) {
            $user = User::where('email', '=', $request->email)->first();
            if (isset($user->id)) {
                if (Hash::check($request->password, $user->password)) {
                    foreach ($user->tokens as $token) { //Iterate token list
                        if ($token->last_used_at === null) { //Only revoke never used tokens
                            $token->delete();
                        }
                    }
                    //Create new token
                    $token = $user->createToken('auth_token')->plainTextToken;
                    return response()->json([
                        'message' => "Successful authentication",
                        'access_token' => $token,
                    ], 200);
                } else { //Invalid credentials
                    return $this->getResponse401();
                }
            } else { //User not found
                return $this->getResponse401();
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function changePassword(Request $request)
    {
        $auth = auth()->user();
        $user = User::where('id', '=', $auth->id)->first();

        if($request->password == $request->confirmation_password){
            $user->password = Hash::make($request->password);
            $user->update();
            $this->logout($request);
            return $this->getResponse201("User","Updated", $user);
        }else{
            return response()->json([
                'message' => "Error updated password",]);
        }
    }

    public function userProfile()
    {
        return $this->getResponse200(auth()->user());
    }



    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); //Revoke all tokens
        return response()->json([
            'message' => "Logout successful"
        ], 200);
    }

    // public function logout()
    // {
    //     auth()->tokens()->delete();
    //     return response()->json([
    //         'message' => "Logout succesful"
    //     ],200);
    // }
}
