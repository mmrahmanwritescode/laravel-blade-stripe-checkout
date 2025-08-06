<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items.food'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('front.orders.index', compact('orders'));
    }
    
    public function confirm($orderId) {
        return view('front.orders.confirm', compact('orderId') );
    }
}
