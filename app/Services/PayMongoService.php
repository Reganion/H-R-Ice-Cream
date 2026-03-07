<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayMongoService
{
    Public function createPaymentIntent($amount, $description) {}
    
    public function createGcashResource($paymentIntentId, $amount, $successUrl, $failedUrl) {}

    public function getGCashCheckoutUrl($source) {}

    public function markInvoiceAsPaid($invoiceId) {}
}