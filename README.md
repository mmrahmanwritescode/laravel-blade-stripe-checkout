## Laravel 10 Custom Stripe Checkout with Webhook Integration

This is a custom Laravel checkout system with Stripe payment processing and real-time webhook integration featuring Stripe Payment Intents, SCA (Strong Customer Authentication) support, and automatic order status synchronization via webhooks. 

Built with Laravel 10, Blade templates, Bootstrap, jQuery, and MySQL. 

## Screenshots

![Laravel blade stripe checkout](https://laravelcs.com/images/github/laravel-blade-stripe-checkout.png)

## Following features are implemented

- Schema migration created for users, carts, orders, order_items and food_items table
- Demo data seeded for users and food_items table with related factory and DatabaseSeeder
- root page creates dummy cart items taking food items and redirects to checkout page
- checkout page displays forms for taking user's billing details and shows cart itemss side by side.
- Validations are added with jquery validation plugin and stripe client side JS api to enhance user experience
- During stripe payment process with ajax various payment types are created and processed with Stipe PHP api using cusomly created laravel service StripeService  
- When transaction fails it shows failed message just under checkout submit button
- Page redirects to order confirm page if transaction is successful
- This stripe integration also supports SCA ( Strong Customer Authentication ) when 3d secure authenticatoin is required
- **NEW: Stripe Webhook Integration** - Automatic order status updates via Stripe webhooks for real-time payment processing
  - Handles payment events: succeeded, failed, canceled, requires_action, and dispute events
  - Automatic order status synchronization with payment states
  - Secure webhook signature verification
  - Complete event logging and tracking


## How to use

- Clone or download the project
- run [ composer install and npm install]
- create a mysql database and update the name in .env file
- create stripe developer account and use stripe secret and publishable key from there
- **Setup Stripe Webhooks** (see WEBHOOK_SETUP.md for detailed instructions):
  - Add webhook secret to .env file: `STRIPE_WEBHOOK_SECRET=whsec_your_secret`
  - Configure webhook endpoint in Stripe Dashboard: `https://yourdomain.com/stripe/webhook`
  - Select events: payment_intent.succeeded, payment_intent.payment_failed, payment_intent.canceled, payment_intent.requires_action, charge.dispute.created
- run [ php artisan migrate:fresh --seed ]
- [ npm run dev and php artisan serve ]
- browse to http://localhost:8000


## License
Feel free to use and re-use any way you want.

## This repo is also written as tutorial at laravelcodesnippets.com 
- [A Step-by-Step Guide on Laravel Checkout System with Stripe](https://laravelcs.com/communities/projects/topics/stripe/posts/192)

## Webhook Documentation
For detailed webhook setup and configuration instructions, see [WEBHOOK_SETUP.md](WEBHOOK_SETUP.md)



## You can check more tutorials and posts in my site [LaravelCodeSnippet.com](https://laravelcs.com)

## Most viewed Links in Laravelcodesnippets.com

- [Building mini ecommerce in Laravel](https://laravelcs.com/communities/projects/topics/mini-ecommerce/posts/113)
- [Building mini issue trackcer with vue3 spa, authentication and authorization in Laravel](https://laravelcs.com/communities/projects/topics/mini-issue-tracker/posts/159)

## Available for freelance work
Feel free to reach me at [mahfoozurrahman.com](https://www.mahfoozurrahman.com)
