<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayMongoService;
use App\Models\Order;
use App\Models\Invoice;

class ApiOrderPaymentController extends Controller
{
    protected $paymongo;

    public function __construct(PayMongoService $paymongo) 
    {
        $this->paymongo = $paymongo;
    }

    //
    public function createInvoice(Request $request, $orderId) 
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $order = Order::findOrFail($orderId);
        $amount = $request->input('amount');

        // PayMongo PaymentIntent part
        $description = "Payment for Order #" . $order->transaction_id;
        $paymentIntent = $this->paymongo->createPaymentIntent($amount * 100, $description);
        
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'status' => 'pending',
            'payment_intent_id' => $paymentIntent['data']['id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'invoice' => $invoice,
            'payment_intent' => $paymentIntent
        ]);
    }

    public function payWithGcash($invoiceId) 
    {
        
        $invoice = Invoice::findOrFail($invoiceId);
        $order = $invoice->order;

        $successUrl = route('payment.success', ['invoice' => $invoice->id]);
        $failedUrl = route('payment.failed', ['invoice' => $invoice->id]);

        $source = $this->paymongo->createGcashResource(
            $invoice->payment_intent_id,
            $invoice->amount * 100, // amount in centavos
            $successUrl,
            $failedUrl
        );

        $checkoutUrl = $this->paymongo->getGCashCheckoutUrl($source);

        return response()->json([
            'success' => true,
            'checkout_url' => $checkoutUrl
        ]);

    }

    public function handlePaymongoWebhook(Request $request) 
    {
        $payload = $request->all();
        if ($payload['type'] === 'payment_intent.succeeded') {
            $paymentIntentId = $payload['data']['id'] ?? null;

            $invoice = Invoice::where('payment_intent_id', $paymentIntentId)->first();
            if ($invoice) {
                $this->paymongo->markInvoiceAsPaid($invoice->id);

                $order = $invoice->order;

                // Check if all invoices are paid
                $totalPaid = $order->invoices()->where('status', 'paid')->sum('amount');
                if ($totalPaid >= $order->amount) {
                    $order->status = 'paid';
                    $order->save();
                } else {
                    $order->status = 'pending';
                    $order->save();
                }
            }
        }
        return response()->json(['received' => true]);
    }

    public function getInvoiceStatus($invoiceId) {
        $invoice = Invoice::findOrFail($invoiceId);

        $status = $this->paymongo->getPaymentStatus($invoice->payment_intent_id);

        return response()->json([
            'success' => true,
            'invoice_status' => $status,
            'invoice' => $invoice
        ]);
    }

    public function getOrderInvoices($orderId) {
        $order = Order::with('invoices')->findOrFail($orderId);

        return response()->json([
            'success' => true,
            'invoices' => $order->invoices
        ]);
    }
}
