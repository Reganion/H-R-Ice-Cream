<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
class DeliveryService 
{
    public function geocodeAddress($address): array
    {
        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $response = Http::get(
            'https://maps.googleapis.com/maps/api/geocode/json',
            [
                'address' => $address,
                'key' => $apiKey
            ]
        );

        $data = $response->json();

        if (!empty($data['results'][0]['geometry']['location'])) {
            return [
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng'],
            ];
        }

        return ['lat' => null, 'lng' => null];
    }
    
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float 
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;

    }

    public function isSignificantMovement(float $distanceKm, float $thresholdKm = 0.02): bool
    {
        return $distanceKm >= $thresholdKm;
    }

}