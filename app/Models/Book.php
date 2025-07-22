<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;
    protected $table = 'books';

    protected $fillable = [
        'title',
        'author',
        'price',
        'image',
        'publication_date',
        'user_id',
        'status',
        'description',
        'language',
        'borrow_count',
        'borrow_date',
        'return_date'
    ];
}
