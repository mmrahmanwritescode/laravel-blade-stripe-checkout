<?php
namespace App\Services;

use Stripe\StripeClient;
use Illuminate\Support\Facades\Config;

class StripeService
{
    function refund($chargeId, $amount = null) {
        $response = [
            'refundStatus' => false,
            'refundID' => '',
            'api_error' => ''
        ];

        $STRIPE_SECRET_KEY = Config::get('stripe.stripe_secret_key');
        $stripe = new StripeClient($STRIPE_SECRET_KEY);

        try {
            $refund = $stripe->refunds->create([
                'payment_intent' => $chargeId,
                'amount' => $amount ? $amount * 100 : null,
            ]);
        } catch (\Exception $e) {
            $response['api_error'] = $e->getMessage();
            return $response;
        }

        if ($refund->status == 'succeeded') {
            $response['refundID'] = $refund->id;
            $response['refundStatus'] = $refund->status;
        }

        return $response;
    }

    function create_payment_intent($total_price) {
        $response = ['api' => '', 'error' => ''];
        $STRIPE_SECRET_KEY = Config::get('stripe.stripe_secret_key');
        $stripe = new StripeClient($STRIPE_SECRET_KEY);
        $itemPriceCents = round($total_price * 100);

        try {
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $itemPriceCents,
                'currency' => 'USD',
                'description' => 'Total Payment of Order #' . getLastOrderNo(),
                'payment_method_types' => ['card']
            ]);
            $response['api'] = [
                'id' => $paymentIntent->id,
                'clientSecret' => $paymentIntent->client_secret
            ];
        } catch (\Error $e) {
            $response['error'] = $e->getMessage();
        }
        return $response;
    }

    function create_customer($payment_intent_id, $email, $name) {
        $response = ['api' => '', 'error' => ''];
        $STRIPE_SECRET_KEY = Config::get('stripe.stripe_secret_key');
        $stripe = new StripeClient($STRIPE_SECRET_KEY);

        try {
            $paymentIntent = $stripe->paymentIntents->retrieve($payment_intent_id);
            $customer_id = $paymentIntent->customer ?? null;

            if (!$customer_id) {
                $customer = $stripe->customers->create(['name' => $name, 'email' => $email]);
                $customer_id = $customer->id;
            }

            $stripe->paymentIntents->update($payment_intent_id, ['customer' => $customer_id]);
            $response['api'] = ['id' => $payment_intent_id, 'customer_id' => $customer_id];
        } catch (\Error $e) {
            $response['error'] = $e->getMessage();
        }

        return $response;
    }

    public function payment_insert($payment_intent, $customer_id) {
        $response = ['paymentStatus' => false, 'error' => '', 'transactionID' => ''];
        $STRIPE_SECRET_KEY = Config::get('stripe.stripe_secret_key');
        $stripe = new StripeClient($STRIPE_SECRET_KEY);

        try {
            $customer = $stripe->customers->retrieve($customer_id);
            if ($payment_intent["status"] == 'succeeded') {
                $response['paymentStatus'] = true;
                $response['transactionID'] = $payment_intent["id"];
            }
        } catch (\Error $e) {
            $response['error'] = $e->getMessage();
        }

        return $response;
    }
}
