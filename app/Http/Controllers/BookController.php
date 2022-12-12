<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\BookReview;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $books = Book::all();
      //  $books = Book::with("category", "editorial", "authors","bookDownload")->get();
        // return [
        //     "error",
        //     "message" => "Successfull query",

        //     "data" => $books

        // ];
            $books =  Book::with("category","editorial","authors")->get();
            return [
                "error"=> false,
                "message"=> "Succesfull query",
                "data"=> $books
            ];

        $books = Book::orderBy('title', 'asc')->get();
        return $this->getResponse200($books);
    }

    public function response()
    {
        return [
            "error" => true,
            "message" => "Wrong action!",
            "data" => []

        ];
    }

    public function addBookReview(Request $request){
        $auth = auth()->user();
        $user = User::where('id', '=', $auth->id)->first();

        if(!empty($user)){
            $bookReview = new BookReview();
            $bookReview->comment = $request->comment;
            $bookReview->edited = false;
            $bookReview->user_id = $user->id;
            $bookReview->book_id = $request->book_id;

            $bookReview->save();
            return $this->getResponse201("bookReview","created",$bookReview);


        }else{
            return $this->getResponse404;

        }
    }

    public function updateBookReview(Request $request,$id){
        $auth = auth()->user();
        $user = User::where('id', '=', $auth->id)->first();

        $bookReview = BookReview::find($id);

        if($bookReview->id != null){
            if($user->id === $bookReview->id){
                $bookReview->comment = $request->comment;
                $bookReview->edited = true;

                $bookReview->update();
                return $this->getResponse200("bookReview","updated",$bookReview);

            }else{
                return $this->getResponse403();

            }
        }else{
            return $this->getResponse403();
        }



    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = $this->response();
        $isbn = trim($request->isbn);
        $existeIsbn = Book::where("isbn", $isbn)->exists();
        if (!$existeIsbn) {
            $book = new Book();
            $book->isbn = $isbn;
            $book->title = $request->title;
            $book->description = $request->description;
            $book->published_date = Carbon::now();
            $book->category_id = $request->category['id'];
            $book->editorial_id = $request->editorial['id'];
            $book->save();
            foreach ($request->authors as $item) {
                $book->authors()->attach($item);
            }
            // $response["error"] = false;
            // $response["message"] = "Your book has been created!";
            // $response["data"] = $book;

            return $this->getResponse201("book","created",$book);
        } else {
           // $response["message"] = "ISBN duplicated!";
           return $this->getResponse500;
        }
        //return $response;
        $this->getResponse201;
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $response = $this->response();
        $book = Book::find($id);
        DB::beginTransaction();
        try {
            if ($book) {
                $isbn = trim($request->isbn);
                $IsbnOwner = Book::where("isbn", $isbn)->first();
                if (!$IsbnOwner || $IsbnOwner->id == $book->id) {
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = Carbon::now();
                    $book->category_id = $request->category['id'];
                    $book->editorial_id = $request->editorial['id'];
                    $book->update();
                    //Delete
                    foreach ($book->authors as $item) {
                        $book->authors()->detach($item->id);
                    }
                    //Add new authors
                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    //  $book->update();
                    $book = Book::with("category", "editorial", "authors")->where("id", $id)->get();
                    // $response["error"] = false;
                    // $response["message"] = "Your book has been updated!";
                    // $response["data"] = $book;
                    return $this->getResponse200($book);
                } else {
                    return $this->getResponse500;
                   // $response["message"] = "ISBN duplicated!!!";
                }
            } else {
                return $this->getResponse404;
              //  $response["message"] = "Not Found";
            }

            DB::commit();
        } catch (Exception $e) {
            // $response["message"] = "RollBack transaction";
            return $this->getResponse500;
            DB::rollBack();
        }
        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy(Book $book)
    {
        //
    }
}
