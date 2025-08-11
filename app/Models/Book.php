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
        'author_id',
        'price',
        'image',
        'quantity',
        'publication_date',
        'description',
        'language',
        'category_id',
        'discount_price',
        'new_best_seller',
        'weight_in_grams',
        'packaging_size_cm',
        'number_of_pages',
        'form',
        'state',
    ];
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
