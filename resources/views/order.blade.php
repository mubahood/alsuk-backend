<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Order Details - #{{ $order->id }}</h4>


        </div>
        <div class="card-body">

            <a href="{{ admin_url('orders/' . $order->id . '/edit') }}" class="btn btn-warning">Update Order
                Status</a> <br><br>
            <h5 class="card-title">Customer Information</h5>
            <p><strong>Name:</strong> {{ $order->customer_name }}</p>
            <p><strong>Email:</strong> {{ $order->mail }}</p>
            <p><strong>Phone Number 1:</strong> {{ $order->customer_phone_number_1 }}</p>
            @if ($order->customer_phone_number_2)
                <p><strong>Phone Number 2:</strong> {{ $order->customer_phone_number_2 }}</p>
            @endif
            <p><strong>Address:</strong> {{ $order->customer_address }}</p>

            <hr>

            <h5 class="card-title">Order Information</h5>
            <p><strong>Order ID:</strong> {{ $order->id }}</p>
            <p><strong>Order State:</strong> {{ $order->order_state == 0 ? 'Pending' : 'Completed' }}</p>
            <p><strong>Date Created:</strong> {{ $order->created_at }}</p>
            <p><strong>Last Updated:</strong> {{ $order->updated_at }}</p>
            <p><strong>Description:</strong> {{ $order->description }}</p>
            <p><strong>Order Total:</strong> CA$ {{ number_format($order->order_total, 2) }}</p>

            <hr>

            <h5 class="card-title">Order Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Amount (R)</th>
                            <th>Color</th>
                            <th>Size</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->get_items() as $item)
                            <tr>
                                <td>{{ $item->pro->id }}</td>
                                <td>{{ $item->pro->name }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->amount, 2) }}</td>
                                <td>{{ $item->color }}</td>
                                <td>{{ $item->size }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>

            <h5 class="card-title">Payment Information</h5>
            <p><strong>Payment Confirmation:</strong>
                {{ $order->payment_confirmation ? 'Confirmed' : 'Not Confirmed' }}</p>
            <p><strong>Stripe ID:</strong> {{ $order->stripe_id ?? 'N/A' }}</p>
            <p><strong>Stripe Payment Status:</strong> {{ $order->stripe_paid ? 'Paid' : 'Not Paid' }}</p>
            @if ($order->stripe_url)
                <p><strong>Stripe URL:</strong> <a href="{{ $order->stripe_url }}" target="_blank">View Payment</a></p>
            @endif

            <hr>

            <h5 class="card-title">Delivery Information</h5>
            <p><strong>Delivery District:</strong> {{ $order->delivery_district }}</p>
            <p><strong>Delivery Method:</strong> {{ $order->delivery_method ?? 'N/A' }}</p>
            <p><strong>Delivery Address:</strong> {{ $order->delivery_address_details ?? 'N/A' }}</p>
            <p><strong>Delivery Amount:</strong> R
                {{ $order->delivery_amount ? number_format($order->delivery_amount, 2) : '0.00' }}</p>

            <hr>

            <h5 class="card-title">Final Amount Payable</h5>
            <p><strong>Total Amount Payable:</strong> R
                {{ number_format($order->payable_amount ?? $order->order_total, 2) }}</p>
        </div>

        <a href="{{ admin_url('orders/' . $order->id . '/edit') }}" class="btn btn-warning">Update Order
            Status</a>

    </div>
</div>
