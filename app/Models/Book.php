<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    protected $table = "books";
    protected $fillable = [
        "id",
        "isbn",
        "title",
        "description",
        "published_date",
        "category_id",
        "editorial_id"
    ];

    public $timestamps = false;

    public function bookDownload(){
        return $this->hasOne(BookDownload::class);
    }

    public function category(){
       // return $this->hasOne(Category::class, "id");
       return $this->belongsTo(Category::class, "category_id", "id");
    }

    public function editorial(){
        // return $this->hasOne(Category::class, "id");
        return $this->belongsTo(Category::class, "editorial_id", "id");
     }

    public function authors(){
        // return $this->hasOne(Category::class, "id");
        return $this->belongsToMany(Author::class,
         "authors_books",
         "books_id",
         "authors_id");
     }
}
