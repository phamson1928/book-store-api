<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'order_items';
    
    protected $fillable = [
        'order_id',
        'book_id', 
        'quantity', 
        'price'
    ];
    
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
