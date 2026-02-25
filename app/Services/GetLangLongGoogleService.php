<?php

namespace App\Services;

use App\Model\Addressdata;
use Illuminate\Support\Facades\Log;

class GetLangLongGoogleService
{
    // Get latitude and longitude for a given address using Google Maps API
    // Caches result in DB if not already present
    public function getLatLngFromGoogleService(string $address): array
    {
        // Return cached data if available
        if ($cached = $this->getCachedAddress($address)) {
            return $cached;
        }

        $response = $this->callGoogleApi($address);

        if (! $response) {
            return ['lat' => null, 'long' => null];
        }

        $data = $this->parseGoogleResponse($response);

        if ($data['lat'] && $data['long']) {
            $this->cacheAddress($address, $data['lat'], $data['long']);
        }

        return $data;
    }

    private function getCachedAddress(string $address): ?array
    {
        $addressData = Addressdata::where('address', $address)->first();

        return $addressData ? [
            'lat' => $addressData->latitude,
            'long' => $addressData->longitude,
        ] : null;
    }

    private function callGoogleApi(string $address): ?object
    {
        $apiKey = env('GOOGLE_MAP_API_KEY');
        $url = 'https://maps.google.com/maps/api/geocode/json?address='
            .urlencode($address).'&key='.$apiKey;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 20,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            Log::error('Google API CURL Error: '.curl_error($ch));
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return json_decode($response);
    }

    private function parseGoogleResponse(object $response): array
    {
        if (isset($response->status) && $response->status === 'OK'
            && isset($response->results[0]->geometry->location)) {
            $loc = $response->results[0]->geometry->location;
            return [
                'lat' => $loc->lat ?? null,
                'long' => $loc->lng ?? null,
            ];
        }
        return ['lat' => null, 'long' => null];
    }

    private function cacheAddress(string $address, float $lat, float $lng): void
    {
        Addressdata::create([
            'address' => $address,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }
}
