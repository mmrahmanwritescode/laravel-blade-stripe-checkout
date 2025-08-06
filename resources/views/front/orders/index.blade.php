@extends('layouts.app')
@section('content')

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title">Order List</h4>
                        <div class="text-muted">
                            Total Orders: {{ $orders->total() }}
                        </div>
                    </div>

                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total Price</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                        <th>Order Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>#{{ $order->purchase_order_id ?? $order->id }}</strong>
                                            </td>
                                            <td>
                                                @if($order->user)
                                                    <div>
                                                        <strong>{{ $order->user->name }}</strong><br>
                                                        <small class="text-muted">{{ $order->user->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Guest Order</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->items->count() > 0)
                                                    <div class="small">
                                                        @foreach($order->items as $item)
                                                            <div>{{ $item->food->name ?? 'Unknown Item' }} (x{{ $item->quantity }})</div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No items</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>&euro;{{ number_format($order->price, 2) }}</strong>
                                                @if($order->shipping_cost > 0)
                                                    <br><small class="text-muted">+ &euro;{{ number_format($order->shipping_cost, 2) }} shipping</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = 'badge bg-secondary';
                                                    switch($order->status) {
                                                        case 'pending':
                                                            $statusClass = 'badge bg-warning';
                                                            break;
                                                        case 'completed':
                                                            $statusClass = 'badge bg-success';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'badge bg-danger';
                                                            break;
                                                        case 'processing':
                                                            $statusClass = 'badge bg-info';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="{{ $statusClass }}">
                                                    {{ ucfirst($order->status ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $paymentStatusClass = 'badge bg-secondary';
                                                    switch($order->payment_status) {
                                                        case 'paid':
                                                        case 'succeeded':
                                                            $paymentStatusClass = 'badge bg-success';
                                                            break;
                                                        case 'pending':
                                                            $paymentStatusClass = 'badge bg-warning';
                                                            break;
                                                        case 'failed':
                                                            $paymentStatusClass = 'badge bg-danger';
                                                            break;
                                                        case 'refunded':
                                                            $paymentStatusClass = 'badge bg-info';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="{{ $paymentStatusClass }}">
                                                    {{ ucfirst($order->payment_status ?? 'Unknown') }}
                                                </span>
                                                @if($order->payment_completed_at)
                                                    <br><small class="text-muted">{{ $order->payment_completed_at->format('M d, Y H:i') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $order->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $order->created_at->format('H:i A') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if($order->transaction_id)
                                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                title="Transaction ID: {{ $order->transaction_id }}">
                                                            <i class="fas fa-receipt"></i>
                                                        </button>
                                                    @endif
                                                    @if($order->notes)
                                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                                title="{{ $order->notes }}">
                                                            <i class="fas fa-sticky-note"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No Orders Found</h5>
                            <p class="text-muted">There are currently no orders in the system.</p>
                            <a href="{{ route('cart.create') }}" class="btn btn-primary">Start Shopping</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .table th {
        border-top: none;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    .table-responsive {
        border-radius: 0.375rem;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .table td, .table th {
            padding: 0.5rem 0.25rem;
        }
    }
</style>
@endsection

@section('script')
<script>
    // Add any JavaScript functionality here if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips if Bootstrap tooltip is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });
</script>
@endsection
