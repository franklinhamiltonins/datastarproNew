<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Model\Addressdata;

class GetLangLongGoogleService
{

    public function getLatLngFromGoogleService($address)
    {
        $address_data = Addressdata::where('address', $address)->first();
        if ($address_data) {
            $data['lat'] = $address_data->latitude;
            $data['long'] = $address_data->longitude;
            return $data;
        }
        //Google Map API URL
        $API_KEY = env('GOOGLE_MAP_API_KEY');
        $url = "https://maps.google.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $API_KEY;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response);

        // echo "<pre>";print_r($result);exit;

        $data['lat'] = null;
        $data['long'] = null;
        if ($result && $result->status == "OK") {
            $data['lat'] = $result->results[0]->geometry->location->lat;
            $data['long'] = $result->results[0]->geometry->location->lng;
            Addressdata::create(['address' => $address, 'latitude' => $data['lat'], 'longitude' => $data['long']]);
        }
        return $data;
    }
}