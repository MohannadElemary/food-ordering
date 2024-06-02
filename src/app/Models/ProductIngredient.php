<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductIngredient extends Pivot
{
    protected $table = 'product_ingredients';

    protected $fillable = ['product_id', 'ingredient_id', 'amount'];
}
