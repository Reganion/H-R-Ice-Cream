<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DeliveryService;

class ApiGeoController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService) 
    {
        $this->deliveryService = $deliveryService;
    }

    public function updateLocation(Request $request) {}

}
