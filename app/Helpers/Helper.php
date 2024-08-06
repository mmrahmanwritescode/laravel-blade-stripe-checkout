<?php

use App\Models\Cart;
use App\Models\Order;

function top_cart_query()
{
    $cart_session = session()->get('cart_session');
    return Cart::with(['food_item'])->where('purchase_session_id', $cart_session)->get();
}

function cart_row_total($product_id) {
    $cart_row_summary = Cart::where([
        'purchase_session_id' => session()->get('cart_session'),
        'food_item_id' => $product_id
    ])->get();

    $total = 0;
    foreach ($cart_row_summary as $item) {
        $itemPrice = ($item->discount > 0 ) ? calDiscount($item->price, $item->discount) : $item->price;
        $total += $itemPrice * $item->quantity;
    }
    return number_format($total,2);
}

function cart_summary() {

    $cart = top_cart_query();
    $total = 0;
    $qty = 0;
    foreach ($cart as $item) {
        $itemPrice = ($item->discount > 0 ) ? calDiscount($item->price, $item->discount) : $item->price;
        $total += $itemPrice * $item->quantity;
        $qty += $item->quantity;
    }
    return [
        'total' => number_format($total,2),
        'qty' => $qty
    ];
}

function clearCart() {
    Cart::where('purchase_session_id', session()->get('cart_session'))->delete();
    session()->forget('cart_session');
}

function calDiscount($price, $discount) {
    return number_format($price - ( $price * $discount / 100 ), 2);
}

function getLastOrderNo() {
    $lastOrder = Order::latest('id')->first();
    return str_pad($lastOrder ? $lastOrder->id + 1 : 1, 5, '0', STR_PAD_LEFT);
}
