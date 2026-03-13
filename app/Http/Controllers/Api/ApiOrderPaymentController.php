<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Flavor;
use App\Models\AdminNotification;
use App\Models\CustomerNotification;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiOrderPaymentController extends Controller
{
    protected $paymongo;

    public function __construct(PayMongoService $paymongo)
    {
        $this->paymongo = $paymongo;
    }

    public function qrindex()
    {
        $paymentIntent = $this->paymongo->createPaymentIntent(100, "Test QRPH Payment");

        $paymentMethod = $this->paymongo->createQrphPaymentMethod(
            'Juan Dela Cruz',
            'juan@example.com',
            '09171234567'
        );

        $attachResponse = $this->paymongo->attachPaymentMethodToIntent(
            $paymentIntent['id'],
            $paymentMethod['id']
        );

        $qrData = $attachResponse['data']['attributes']['next_action']['code']['image_url'] ?? null;

        return view('payment.qrph.qrph', ['qrData' => $qrData]);
    }

    /**
     * Create a PaymentIntent for customer downpayment (QRPH) and a pending invoice.
     * Flutter mobile app calls this after "Place Order" when payment method is Gcash/QRPH.
     *
     * Body (JSON):
     * - product_name, product_type, gallon_size, delivery_date, delivery_time, delivery_address
     * - amount (full order amount), quantity/qty
     * - payment_method ("Gcash" / "QRPH"), downpayment_percent (0.25, 0.5, 0.75, 1.0)
     *
     * Response (200):
     * {
     *   "success": true,
     *   "message": "...",
     *   "data": {
     *     "order_id": 123,
     *     "invoice_id": 10,
     *     "payment_intent_id": "pi_xxx",
     *     "qr_image_url": "https://...",
     *     "downpayment_amount": 500.00
     *   }
     * }
     */
    public function createDownpayment(Request $request): JsonResponse
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'gallon_size' => 'required|string|max:50',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required|string',
            'delivery_address' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'downpayment_percent' => 'required|numeric|min:0.25|max:1.0',
            'quantity' => 'nullable|integer|min:1',
            'qty' => 'nullable|integer|min:1',
        ]);

        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user.',
            ], 401);
        }

        $percent = (float) $request->downpayment_percent;
        $fullAmount = (float) $request->amount;
        $downpaymentAmount = round($fullAmount * $percent, 2);
        $downpaymentCentavos = (int) round($downpaymentAmount * 100);
        $balanceAmount = max(0, $fullAmount - $downpaymentAmount);

        if ($downpaymentCentavos <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Downpayment amount must be greater than zero.',
            ], 422);
        }

        $quantity = max(
            1,
            (int) $request->input('quantity', $request->input('qty', 1))
        );

        $customerFullName = trim($user->firstname . ' ' . $user->lastname);
        $customerName = $customerFullName !== '' ? $customerFullName : 'Guest';
        $customerPhone = (string) ($user->contact_no ?? '');
        $customerImage = $user->image ?? 'img/default-user.png';

        $flavor = Flavor::where('name', $request->product_name)->first();
        $productImage = $flavor?->image ?? 'img/default-product.png';

        // Note: some existing databases may use an ENUM for `status` that does not
        // include "awaiting_downpayment". To avoid SQL truncation errors, we use
        // "pending" (which already exists in the enum) and let the Invoice status
        // represent whether the downpayment has actually been paid or not.
        $order = Order::create([
            'customer_id' => $user->id,
            'transaction_id' => strtoupper(Str::random(10)),
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'gallon_size' => $request->gallon_size,
            'product_image' => $productImage,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_image' => $customerImage,
            'delivery_date' => $request->delivery_date,
            'delivery_time' => $request->delivery_time,
            'delivery_address' => $request->delivery_address,
            'amount' => $fullAmount,
            'downpayment' => $downpaymentAmount,
            'balance' => $balanceAmount,
            'qty' => $quantity,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);

        $description = sprintf(
            'Downpayment %.0f%% for Order #%s',
            $percent * 100,
            $order->transaction_id
        );

        $paymentIntent = $this->paymongo->createPaymentIntent($downpaymentCentavos, $description);
        if (!$paymentIntent || !isset($paymentIntent['id'])) {
            $order->update(['status' => 'cancelled', 'reason' => 'Failed to create payment intent.']);

            return response()->json([
                'success' => false,
                'message' => 'Could not initialize payment. Please try again.',
            ], 502);
        }

        $paymentMethod = $this->paymongo->createQrphPaymentMethod(
            $customerName,
            $user->email,
            $customerPhone
        );

        if (!$paymentMethod || !isset($paymentMethod['id'])) {
            $order->update(['status' => 'cancelled', 'reason' => 'Failed to create QRPH payment method.']);

            return response()->json([
                'success' => false,
                'message' => 'Could not initialize payment method. Please try again.',
            ], 502);
        }

        $attachResponse = $this->paymongo->attachPaymentMethodToIntent(
            $paymentIntent['id'],
            $paymentMethod['id']
        );

        $qrImageUrl = $attachResponse['data']['attributes']['next_action']['code']['image_url'] ?? null;
        if (!$qrImageUrl) {
            $order->update(['status' => 'cancelled', 'reason' => 'Failed to get QR code image.']);

            return response()->json([
                'success' => false,
                'message' => 'Could not generate QR code. Please try again.',
            ], 502);
        }

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntent['id'],
            'source_id' => $paymentMethod['id'],
            'amount' => $downpaymentAmount,
            'currency' => 'PHP',
            'status' => 'pending',
            'payment_method' => 'qrph',
        ]);

        CustomerNotification::create([
            'customer_id'   => $user->id,
            'type'          => CustomerNotification::TYPE_ORDER_PLACED,
            'title'         => $order->product_name,
            'message'       => 'Please complete the downpayment to confirm your order.',
            'image_url'     => $productImage,
            'related_type'  => 'Order',
            'related_id'    => $order->id,
            'data'          => ['transaction_id' => $order->transaction_id],
        ]);

        AdminNotification::createForAllAdmins(
            AdminNotification::TYPE_ORDER_NEW,
            $customerName,
            null,
            $productImage,
            'Order',
            $order->id,
            [
                'subtitle' => 'Order #' . $order->transaction_id,
                'highlight' => $order->product_name . ' (awaiting downpayment)',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Downpayment initialized. Scan the QR code to pay.',
            'data' => [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'payment_intent_id' => $paymentIntent['id'],
                'qr_image_url' => $qrImageUrl,
                'downpayment_amount' => $downpaymentAmount,
                'balance' => $balanceAmount,
            ],
        ]);
    }

    /**
     * Check PayMongo status for a downpayment and update invoice/order.
     * GET /api/v1/orders/downpayment/status/{invoice}
     */
    public function checkDownpaymentStatus(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user.',
            ], 401);
        }

        $order = $invoice->order;
        if (!$order || (int) $order->customer_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        if (!$invoice->payment_intent_id) {
            return response()->json([
                'success' => false,
                'message' => 'No payment intent found for this invoice.',
            ], 422);
        }

        $status = $this->paymongo->getPaymentStatus($invoice->payment_intent_id);

        if ($status === 'succeeded' && $invoice->status !== 'paid') {
            $invoice->status = 'paid';
            $invoice->save();

            // Track received amount and recompute remaining balance on the order.
            $currentReceived = (float) ($order->received_amount ?? 0.0);
            $newReceived = $currentReceived + (float) $invoice->amount;
            $order->received_amount = $newReceived;
            $order->balance = max(0, (float) $order->amount - $newReceived);
            $order->save();

            // If order is still in an initial state (e.g. pending), keep it as pending.
            // We don't change non-initial states here.
        } elseif (in_array($status, ['failed', 'cancelled'], true) && $invoice->status !== 'failed') {
            $invoice->status = 'failed';
            $invoice->save();

            if ($order->status === 'pending') {
                $order->status = 'cancelled';
                $order->reason = 'Downpayment failed or cancelled.';
                $order->save();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'invoice_status' => $invoice->status,
                'order_status' => $order->status,
                'payment_status' => $status,
                'order_balance' => $order->balance,
                'order_received_amount' => $order->received_amount,
            ],
        ]);
    }

    /**
     * Cancel a pending downpayment from the app (X/Close button on QR screen).
     * POST /api/v1/orders/downpayment/cancel/{invoice}
     */
    public function cancelDownpayment(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();
        if (!$user instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user.',
            ], 401);
        }

        $order = $invoice->order;
        if (!$order || (int) $order->customer_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Downpayment is already paid and cannot be cancelled.',
            ], 422);
        }

        $invoice->status = 'failed';
        $invoice->save();

        if ($order->status === 'pending') {
            $order->status = 'cancelled';
            $order->reason = 'Customer closed payment screen before completing downpayment.';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Downpayment has been cancelled.',
            'data' => [
                'invoice_status' => $invoice->status,
                'order_status' => $order->status,
            ],
        ]);
    }
}