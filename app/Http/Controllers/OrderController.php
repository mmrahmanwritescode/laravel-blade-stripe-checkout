<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function confirm($orderId) {
        return view('front.orders.confirm', compact('orderId') );
    }
}
