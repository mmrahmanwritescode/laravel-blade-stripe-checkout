@extends('layouts.app')

@section('content')

    <div class="container mt-5">
        <div class="row align-center justify-content-center">
            <div class="col-lg-10 offset-lg-1">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Order Confirmed!</h5>
                        <p class="text">Thank you for your order. Your order has been received.</p>
                        <a href="/" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script type="text/javascript">
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
        };
    </script>
@endsection
