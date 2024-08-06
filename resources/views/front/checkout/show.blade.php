@extends('layouts.app')
@section('content')

    @if($customerId && $orderId)
        <div class="container mt-5">
            <div class="row align-center justify-content-center">
                <div class="col-lg-10 offset-lg-1">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-4">
                                <img src="/front/img/shop/transaction.jpg" alt="" class="img-fluid">
                            </div>
                            <h5 class="card-title">Transaction is in progress</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="contact-style-one-area default-padding overflow-hidden mt-5">
            <div class="container">
                <ul class="alert alert-danger bg-gray d-none" role="alert">
                    <li class="text-center"></li>
                </ul>
                <a id="vali-error"></a>
                <form action="{{ route('checkout.store') }}" method="POST" class="contact-form" id="shopping-cart-frm">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h4 class="mb-4">Billing Details</h4>
                                    <div class="row">
                                        <div class="col-lg-6 mb-3">
                                            <div class="form-group">
                                                <input class="form-control" id="first_name" name="first_name" placeholder="enter first name*" type="text" value="{{ old('first_name')  }}">
                                                @if ($errors->has('first_name'))
                                                    <span class="text-danger">{{ $errors->first('first_name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-3">
                                            <div class="form-group">
                                                <input class="form-control" id="last_name" name="last_name" placeholder="enter last name*" type="text" value="{{ old('last_name')  }}">
                                                @if ($errors->has('last_name'))
                                                    <span class="text-danger">{{ $errors->first('last_name') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="form-group">
                                                <input class="form-control" id="email" name="email" placeholder="email*" type="email" value="{{ old('email')  }}">
                                                @if ($errors->has('email'))
                                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="form-group">
                                                <input class="form-control" id="phone" name="phone" placeholder="enter phone*" type="text" value="{{ old('phone')  }}">
                                                @if ($errors->has('phone'))
                                                    <span class="text-danger">{{ $errors->first('phone') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="form-group">
                                                <input class="form-control" id="address" name="address" placeholder="enter address*" type="text" value="{{ old('address')  }}">
                                                @if ($errors->has('address'))
                                                    <span class="text-danger">{{ $errors->first('address') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="form-group">
                                                <input class="form-control" id="post_code" name="post_code" placeholder="enter post code*" type="text" value="{{ old('post_code')  }}">
                                                @if ($errors->has('post_code'))
                                                    <span class="text-danger">{{ $errors->first('post_code') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="form-group">
                                                <textarea name="notes" class="form-control" id="notes" placeholder="enter notes(optional)">{{ old('notes') }}</textarea>
                                            </div>
                                        </div>
                                        <div id="paymentElement" class="col-12 mt-4">
                                            <!-- Stripe.js injects the Payment Element -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h4 class="text-center">Your Cart</h4>
                                    <div class="order-detail">
                                        @php
                                            $total_price = 0;
                                            $shippingCost = 0;
                                            $nonDiscountTotal = 0;
                                            $isDiscounted = false;
                                        @endphp
                                        @forelse($cartItems as $cartItem)
                                            @php
                                                if($cartItem->discount > 0) { $isDiscounted = true; }
                                                $itemPrice = ($cartItem->discount > 0) ? calDiscount($cartItem->price, $cartItem->discount) : $cartItem->price;
                                                $subtotal = $cartItem->quantity * $itemPrice;
                                                $total_price += $subtotal;
                                                $nonDiscountTotal += $cartItem->price * $cartItem->quantity;
                                            @endphp
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ $cartItem->food_item->name }}</h6>
                                                    <div class="price">
                                                        @if($cartItem->food_item->discount > 0)
                                                            <small><del>&euro;{{ number_format($cartItem->price * $cartItem->quantity, 2) }}</del></small><br>
                                                            @endif
                                                            &euro;{{ number_format($subtotal, 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p>No items</p>
                                        @endforelse
                                        <table class="table">
                                            <tbody>
                                            <tr class="subtotal">
                                                <td>Cart subtotal</td>
                                                <td class="text-end">
                                                    @if($nonDiscountTotal > 0 && $isDiscounted)
                                                        <small><del>&euro;{{ number_format($nonDiscountTotal, 2) }}</del></small>&nbsp;
                                                        @endif
                                                        &euro;{{ number_format($total_price, 2) }}
                                                </td>
                                            </tr>
                                            <tr class="title">
                                                <td><h6 class=" font-weight-500">Order Type</h6></td>
                                                <td></td>
                                            </tr>
                                            <tr class="">
                                                <td>
                                                    @php
                                                        if( $deliveryType == 'take_away') {
                                                            $tchecked = 'checked="checked"';
                                                        } else if( $deliveryType == 'delivery') {
                                                            $dchecked = 'checked="checked"';
                                                        }else if( $deliveryType == 'pay_on_spot') {
                                                            $ptchecked = 'checked="checked"';
                                                        }else {
                                                            $dchecked = 'checked="checked"';
                                                        }
                                                    @endphp
                                                    <label class="radio">
                                                        <input type="radio" {{ $dchecked ?? '' }} name="order_type" value="delivery"> Delivery
                                                        <span class="checkround"></span>
                                                    </label>
                                                    <label class="radio">
                                                        <input type="radio" {{ $tchecked ?? '' }} name="order_type" value="take_away"> Take away
                                                        <span class="checkround"></span>
                                                    </label>
                                                    <label class="radio">
                                                        <input type="radio" {{ $ptchecked ?? '' }} name="order_type" value="pay_on_spot"> Pay on Spot
                                                        <span class="checkround"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            <tr class="shipping">
                                                <td>Shipping</td>
                                                <td class="text-end">
                                                    &euro;{{ number_format($shippingCost, 2) }}
                                                    <input type="hidden" id="shipping_cost" name="shipping_cost" value="{{ $shippingCost }}">
                                                </td>
                                            </tr>
                                            <tr class="total">
                                                <td>Cart Total</td>
                                                <td class="text-end">
                                                    &euro;{{ number_format($total_price + $shippingCost, 2) }}
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <button type="submit" id="checkout-btn" class="btn btn-primary w-100">Submit</button>
                                        <div id="paymentResponse"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@section('styles')

    <style>
        #checkout-btn{
            display: none;
        }

        #shopping-cart-frm input.error, select.error{
            border: 1px solid red !important;
        }

        #shopping-cart-frm label.error{
            color : red;
            margin-top:3px;
        }

    </style>

@endsection

@section('script')
    <script src="/js/jquery-validate.js" defer></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="/js/checkout.js" STRIPE_PUBLISHABLE_KEY="{{ env('STRIPE_PUBLISHABLE_KEY')  }}" defer></script>
@endsection
