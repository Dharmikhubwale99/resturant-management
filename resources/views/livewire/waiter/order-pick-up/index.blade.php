<div>
    <div class="flex justify-end items-center">
        <a href="{{ route('waiter.order-pickup.create')}}">Create Pickup Ord</a>
    </div>
    @foreach ($orders as $order)
        <h6 class="text-lg font-semibold mb-4">
            Order #{{ $order->id }} -

        <h6 class="text-lg font-semibold mb-4">
            Coustomer Name:- {{ $order->customer_name }}
    @endforeach
</div>
