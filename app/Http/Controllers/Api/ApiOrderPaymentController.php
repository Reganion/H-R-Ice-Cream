<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayMongoService;
use Illuminate\Http\Request;

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
}