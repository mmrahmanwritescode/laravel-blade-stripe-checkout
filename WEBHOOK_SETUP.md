# Stripe Webhook Setup Guide

This guide will help you set up Stripe webhooks for your Laravel application to handle payment events automatically.

## Features Added

### Database Changes
- Added webhook-related fields to the `orders` table:
  - `payment_intent_id`: Stripe Payment Intent ID
  - `payment_status`: Status from webhook (succeeded, failed, canceled, etc.)
  - `webhook_event_id`: Stripe webhook event ID for tracking
  - `webhook_data`: Complete webhook event data (JSON)
  - `payment_completed_at`: Timestamp when payment was completed

### Webhook Events Handled
- `payment_intent.succeeded`: Payment completed successfully
- `payment_intent.payment_failed`: Payment failed
- `payment_intent.canceled`: Payment was canceled
- `payment_intent.requires_action`: Payment requires additional authentication
- `charge.dispute.created`: Chargeback/dispute created

### Order Status Updates
The webhook automatically updates order status based on payment events:
- `order_placed`: Payment succeeded
- `payment_failed`: Payment failed
- `payment_canceled`: Payment canceled
- `payment_requires_action`: Additional authentication needed
- `disputed`: Chargeback/dispute occurred

## Setup Instructions

### 1. Environment Configuration
Add the webhook secret to your `.env` file:
```env
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

### 2. Stripe Dashboard Configuration
1. Go to your [Stripe Dashboard](https://dashboard.stripe.com/webhooks)
2. Click "Add endpoint"
3. Set the endpoint URL to: `https://yourdomain.com/stripe/webhook`
4. Select the following events to listen for:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `payment_intent.canceled`
   - `payment_intent.requires_action`
   - `charge.dispute.created`
5. Copy the webhook signing secret and add it to your `.env` file

### 3. Security
- The webhook endpoint automatically verifies the signature from Stripe
- CSRF protection is disabled for the webhook route
- All webhook events are logged for debugging

### 4. Testing
You can test webhooks using:
- Stripe CLI: `stripe listen --forward-to localhost:8000/stripe/webhook`
- Stripe Dashboard webhook testing interface
- ngrok for local development: `ngrok http 8000`

## File Changes Made

### New Files
- `app/Http/Controllers/StripeWebhookController.php`: Handles all webhook events
- `database/migrations/2025_08_06_071455_add_webhook_fields_to_orders_table.php`: Database migration

### Modified Files
- `config/stripe.php`: Added webhook secret configuration
- `app/Models/Order.php`: Added new fillable fields and casts
- `routes/web.php`: Added webhook route
- `app/Http/Middleware/VerifyCsrfToken.php`: Excluded webhook from CSRF
- `app/Http/Controllers/CheckoutController.php`: Updated to store payment_intent_id

## Monitoring

### Logs
All webhook events are logged in `storage/logs/laravel.log` with:
- Event type and ID
- Payment status changes
- Order updates
- Error conditions

### Database
Check the `orders` table for:
- Updated payment statuses
- Webhook event data
- Payment completion timestamps

## Troubleshooting

### Common Issues
1. **Signature verification fails**: Check your webhook secret in `.env`
2. **Order not found**: Ensure payment_intent_id is properly stored during checkout
3. **Webhook timeouts**: Stripe requires response within 20 seconds

### Debug Steps
1. Check Laravel logs for webhook events
2. Verify webhook secret matches Stripe dashboard
3. Test with Stripe CLI for local development
4. Ensure database migrations are run

## Important Notes

- Webhooks are idempotent - the same event can be sent multiple times
- Always verify webhook signatures for security
- Store webhook event IDs to prevent duplicate processing
- Test thoroughly in development before production deployment
