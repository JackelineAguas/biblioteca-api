<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $authors = Author::orderBy('name', 'asc')->get();
        return $this->getResponse200($authors);
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
        $author = new Author();
        $author->name = $request->name;
        $author->first_surname = $request->first_surname;
        $author->second_surname = $request->second_surname;
        $author->save();
       return $this->getResponse201("author","created",$author);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author,$id)
    {
        $author = Author::find($id);
        if($author){
            return $this->getResponse200($author);
        }
        return $this->getResponse404();


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function edit(Author $author)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Author $author,$id)
    {
        $author = Author::find($id);
        if($author){
            $author->update($request->all());
            return $this->getResponse200($author);
        }else{
            return $this->getResponse404();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function destroy(Author $author, $id)
    {
        $author = Author::find($id);
        if ($author) {
            $author->delete();
             return $this->getResponse200($author);
            // return [
            //     "status" => true,
            //     "message" => "Successfull query",
            //     "data" => $author
            // ];
        }else{
             return $this->getResponse404();
            // return [
            //     "status" => false,
            //     "message" => "Successfull query",
            //     "data" => $author
            // ];
        }


    }
}
