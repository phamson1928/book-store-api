<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderChangeRequest extends Model
{
    protected $table = 'order_change_requests';
    protected $fillable = [
        'order_id',
        'user_id',
        'note',
        'status',
        'admin_response',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
