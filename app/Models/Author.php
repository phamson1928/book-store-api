<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Author extends Model
{
    use HasFactory;
    protected $table = 'authors';
    protected $fillable = ['name', 'age', 'gender', 'image', 'description', 'nationality', 'total_work'];
    // public function books()
    // {
    //     return $this->hasMany(Book::class);
    // }
}
