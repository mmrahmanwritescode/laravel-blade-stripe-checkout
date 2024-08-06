<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\FoodItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(){
        session()->put('cart_session', date('YmdHis'));
    }
    public function index() {
        $foodItems = FoodItem::all();

        foreach ( $foodItems as $foodItem) {
            Cart::create([
                'purchase_session_id' => session()->get('cart_session'),
                'food_item_id' => $foodItem->id,
                'quantity' => rand(1, 3),
                'price' => $foodItem->price,
            ]);
        }
        //dd(Cart::get());
        return redirect(route('checkout.show'));
    }

    public function clear_cart(){
        Cart::where('purchase_session_id', session()->get('cart_session'))->delete(); //exit;
        return redirect()->back();
    }
}
