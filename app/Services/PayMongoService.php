<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Invoice;

class PayMongoService
{
    public function createPaymentIntent($amount, $description) 
    {
        $secretKey = env('PAYMONGO_SECRET_KEY');

        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.paymongo.com/v1/payment_intents', [
                'data' => [
                    'attributes' => [
                        'amount' => $amount, // in centavos (₱10 = 1000)
                        'currency' => 'PHP',
                        'payment_method_allowed' => ['gcash'],
                        'payment_method_options' => [
                            'gcash' => ['type' => 'redirect']
                        ],
                        'description' => $description,
                    ]
                ]
            ]);

        return $response->json();
    }
    
    public function createGcashResource($paymentIntentId, $amount, $successUrl, $failedUrl) 
    {
        $secretKey = env('PAYMONGO_SECRET_KEY');

        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.paymongo.com/v1/sources', [
                'data' => [
                    'attributes' => [
                        'type' => 'gcash',
                        'amount' => $amount,
                        'currency' => 'PHP',
                        'redirect' => [
                            'success' => $successUrl,
                            'failed' => $failedUrl,
                        ],
                        'payment_intent' => $paymentIntentId,
                    ]
                ]
            ]);

        return $response->json();
    }

    public function getGCashCheckoutUrl($source) 
    {
        return $source['data']['attributes']['redirect']['checkout_url'] ?? null;
    }

    public function markInvoiceAsPaid($invoiceId) 
    {
        $invoice = Invoice::find($invoiceId);

        if ($invoice) {
            $invoice->status = 'paid';
            $invoice->save();
            return true;
        }
        return false;
    }

    public function getPaymentStatus($paymentIntentId)
    {
        $secretKey = env('PAYMONGO_SECRET_KEY');

        $response = Http::withBasicAuth($secretKey, '')
            ->get("https://api.paymongo.com/v1/payment_intents/{$paymentIntentId}");

        return $response->json()['data']['attributes']['status'] ?? null;
    }
}