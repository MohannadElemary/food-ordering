<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrderProduct extends Pivot
{
    protected $table = 'order_products';

    protected $fillable = ['order_id', 'product_id', 'quantity'];
}
