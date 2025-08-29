<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LeafletJSServices
{
    public $searchUrl = 'https://nominatim.openstreetmap.org/search';


    public function searchQuery($query)
    {
        $response = Http::withHeaders([
            'User-Agent' => 'I-REAP_BIDDING (mojicamarcallen@gmail.com)',
            'Accept-Language' => 'en',
        ])->get($this->searchUrl, [
            'q' => $query,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => 5,
            'countrycodes' => 'ph',
            'viewbox' => '116.931885,21.321780,126.604385,4.215806',
            'bounded' => 1,
        ]);

        return $response->json();
    }


}
