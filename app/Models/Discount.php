<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $table = 'discounts';
    protected $fillable = ['discount_percent', 'active', 'created_at', 'updated_at'];
}
