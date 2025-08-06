<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'user_id',
        'status',
        'payment_method',
        'shipping_cost',
        'transaction_id',
        'price',
        'notes',
        'order_type',
        'refund_status',
        'payment_intent_id',
        'payment_status',
        'webhook_event_id',
        'webhook_data',
        'payment_completed_at'
    ];

    protected $casts = [
        'webhook_data' => 'array',
        'payment_completed_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items() {
        return $this->hasMany(OrderItem::class);
    }
}
