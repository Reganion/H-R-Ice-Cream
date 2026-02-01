<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flavor;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminOrderController extends Controller
{
    public function storeWalkIn(Request $request)
    {
        $request->validate([
            'product_name'     => 'required|string|max:255',
            'product_type'     => 'required|string|max:255',
            'gallon_size'      => 'required|string|max:50',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'required',
            'delivery_address' => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
        ]);

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? 'img/default-product.png';

        Order::create([
            'transaction_id'   => strtoupper(Str::random(10)),
            'product_name'     => $request->product_name,
            'product_type'     => $request->product_type,
            'gallon_size'      => $request->gallon_size,
            'product_image'    => $productImage,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'customer_image'   => 'img/default-user.png',
            'delivery_date'    => $request->delivery_date,
            'delivery_time'    => $request->delivery_time,
            'delivery_address' => $request->delivery_address,
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'status'           => 'walk_in',
        ]);

        return back()->with('success', 'Walk-in order added successfully.');
    }

    public function updateWalkIn(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'product_name'     => 'required|string|max:255',
            'product_type'     => 'required|string|max:255',
            'gallon_size'      => 'required|string|max:50',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'required',
            'delivery_address' => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|string|max:50',
        ]);

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? $order->product_image ?? 'img/default-product.png';

        $order->update([
            'product_name'     => $request->product_name,
            'product_type'     => $request->product_type,
            'gallon_size'      => $request->gallon_size,
            'product_image'    => $productImage,
            'customer_name'    => $request->customer_name,
            'customer_phone'   => $request->customer_phone,
            'delivery_date'    => $request->delivery_date,
            'delivery_time'    => $request->delivery_time,
            'delivery_address' => $request->delivery_address,
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
        ]);

        return back()->with('success', 'Walk-in order updated successfully.');
    }
}
