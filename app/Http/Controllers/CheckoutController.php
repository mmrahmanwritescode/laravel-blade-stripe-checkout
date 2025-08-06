<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\StripeService;

class CheckoutController extends Controller
{
    //
    public function index() {
        $cartItems = top_cart_query();
        $deliveryType = 'delivery';

        $orderId = request('order_id', '');
        $customerId = request('customer_id', '');

        return view('front.checkout.show' , compact('cartItems' , 'orderId' , 'customerId' , 'deliveryType') );
    }

    public function store(Request $request) {

        $order = $this->saveTempOrder($request);
        $order->update(['status' => 'order_placed', 'transaction_id' => 'N/A']);
        clearCart();

        return redirect()->route('orders.confirm', $order->purchase_order_id);
    }

    public function payment_init(Request $request, StripeService $stripeService) {

        $response = ['paymentStatus' => false, 'error' => '', 'transactionID' => ''];

        if ($request->request_type == 'create_payment_intent') {

            $total_price = cart_summary()['total'] + $request->shipping_cost;
            $response = $stripeService->create_payment_intent($total_price);

        } elseif ($request->request_type == 'create_customer') {

            $order = $this->saveTempOrder($request);
            $response = $stripeService->create_customer($request->payment_intent_id, $request->email, $request->first_name . ' ' . $request->last_name);
            $response['api']['order_id'] = $order->id;
            
            // Update order with payment intent ID
            $order->update(['payment_intent_id' => $request->payment_intent_id]);

        } 
        elseif ($request->request_type == 'payment_insert') {

            $order = Order::find($request->order_id);
            $response = $stripeService->payment_insert($request->payment_intent, $request->customer_id);
            if ($response['paymentStatus']) {
                $order->update(['transaction_id' => $response['transactionID']]);
                clearCart();
            }
        }

        if ($response['error']) {
            http_response_code(500);
        }
        echo json_encode($response);
    }

    public function saveTempOrder($request) {
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'post_code' => $request->post_code,
            ]
        );

        $order = Order::create([
            'user_id' => $user->id,
            'purchase_order_id' => getLastOrderNo(),
            'status' => 'order_in_progress',
            'payment_method' => ($request->order_type != 'pay_on_spot' ) ? 'stripe' : 'N/A',
            'price' => cart_summary()['total'],
            'shipping_cost' => $request->shipping_cost,
            'transaction_id' => 'N/A',
            'notes' => $request->notes,
            'order_type' => $request->order_type
        ]);

        foreach (top_cart_query() as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'food_item_id' => $item->food_item_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount
            ]);
        }

        return $order;
    }
}
