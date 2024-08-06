<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_session_id', 'food_item_id', 'quantity' , 'price' , 'discount' ];

    public function food_item(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
