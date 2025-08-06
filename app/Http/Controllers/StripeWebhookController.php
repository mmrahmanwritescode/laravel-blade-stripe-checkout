<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Support\Facades\Config;

class StripeWebhookController extends Controller
{
    /**
     * Handle Stripe webhook events
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = Config::get('stripe.webhook_secret');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            //Log::info('Stripe webhook received', ['event' => $event]);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in Stripe webhook', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid signature in Stripe webhook', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', [
            'event_type' => $event->type,
            'event_id' => $event->id
        ]);

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event);
                break;
            
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event);
                break;
            
            case 'payment_intent.canceled':
                $this->handlePaymentIntentCanceled($event);
                break;
            
            case 'payment_intent.requires_action':
                $this->handlePaymentIntentRequiresAction($event);
                break;
            
            case 'charge.dispute.created':
                $this->handleChargeDisputeCreated($event);
                break;
            
            default:
                Log::info('Unhandled Stripe webhook event type', ['type' => $event->type]);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($event)
    {
        $paymentIntent = $event->data->object;
        
        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
        ]);

        // Find order by payment intent ID
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();
        
        if (!$order) {
            Log::warning('Order not found for payment intent', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        // Update order status
        $order->update([
            'status' => 'order_confirmed',
            'payment_status' => 'succeeded',
            'transaction_id' => $paymentIntent->id,
            'webhook_event_id' => $event->id,
            'webhook_data' => $event->data->object,
            'payment_completed_at' => now(),
        ]);

        Log::info('Order updated with successful payment', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id
        ]);
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($event)
    {
        $paymentIntent = $event->data->object;
        
        Log::warning('Payment intent failed', [
            'payment_intent_id' => $paymentIntent->id,
            'last_payment_error' => $paymentIntent->last_payment_error
        ]);

        // Find order by payment intent ID
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();
        
        if (!$order) {
            Log::warning('Order not found for failed payment intent', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        // Update order status
        $order->update([
            'status' => 'payment_failed',
            'payment_status' => 'failed',
            'webhook_event_id' => $event->id,
            'webhook_data' => $event->data->object,
        ]);

        Log::info('Order updated with failed payment', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id
        ]);
    }

    /**
     * Handle canceled payment intent
     */
    private function handlePaymentIntentCanceled($event)
    {
        $paymentIntent = $event->data->object;
        
        Log::info('Payment intent canceled', [
            'payment_intent_id' => $paymentIntent->id
        ]);

        // Find order by payment intent ID
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();
        
        if (!$order) {
            Log::warning('Order not found for canceled payment intent', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        // Update order status
        $order->update([
            'status' => 'payment_canceled',
            'payment_status' => 'canceled',
            'webhook_event_id' => $event->id,
            'webhook_data' => $event->data->object,
        ]);

        Log::info('Order updated with canceled payment', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id
        ]);
    }

    /**
     * Handle payment intent that requires action
     */
    private function handlePaymentIntentRequiresAction($event)
    {
        $paymentIntent = $event->data->object;
        
        Log::info('Payment intent requires action', [
            'payment_intent_id' => $paymentIntent->id
        ]);

        // Find order by payment intent ID
        $order = Order::where('payment_intent_id', $paymentIntent->id)->first();
        
        if (!$order) {
            Log::warning('Order not found for payment intent requiring action', [
                'payment_intent_id' => $paymentIntent->id
            ]);
            return;
        }

        // Update order status
        $order->update([
            'status' => 'payment_requires_action',
            'payment_status' => 'requires_action',
            'webhook_event_id' => $event->id,
            'webhook_data' => $event->data->object,
        ]);

        Log::info('Order updated with payment requiring action', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id
        ]);
    }

    /**
     * Handle charge dispute created
     */
    private function handleChargeDisputeCreated($event)
    {
        $dispute = $event->data->object;
        
        Log::warning('Charge dispute created', [
            'dispute_id' => $dispute->id,
            'charge_id' => $dispute->charge,
            'amount' => $dispute->amount,
            'reason' => $dispute->reason
        ]);

        // Find order by charge ID (transaction_id)
        $order = Order::where('transaction_id', $dispute->charge)->first();
        
        if (!$order) {
            Log::warning('Order not found for disputed charge', [
                'charge_id' => $dispute->charge
            ]);
            return;
        }

        // Update order status
        $order->update([
            'status' => 'disputed',
            'payment_status' => 'disputed',
            'webhook_event_id' => $event->id,
            'webhook_data' => $event->data->object,
        ]);

        Log::info('Order updated with dispute information', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id,
            'dispute_id' => $dispute->id
        ]);
    }
}
