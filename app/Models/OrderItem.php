<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;
    protected $table = 'order_items';
    protected $fillable = ['quantity', 'price'];
    public function book(){
        return $this->belongsTo(Book::class);
    }
    public function order(){
        return $this->belongsTo(Order::class);
    }
}
