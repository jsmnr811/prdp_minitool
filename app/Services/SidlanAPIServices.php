<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SidlanAPIServices
{
    public $apiUrl = 'https://sidlan.da.gov.ph/api/ireap';
    public $apiKey = '9454316226aa6f929d9cf5082d020a49';

    public function executeRequest(array $params = [])
    {
        $defaultParams = [
            'dataset_id'   => 'ir-01-001',
            'return_values' => 'json',
            'cluster'      => 'all',
            'region'       => 'all',
            'province'     => 'all',
            'group_status' => 'all',
            'api_key'      => $this->apiKey,
        ];

        $finalParams = array_merge($defaultParams, $params);

        $response = Http::timeout(300)->get($this->apiUrl, $finalParams);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error' => 'Failed to fetch data from iReap API.',
            'status' => $response->status()
        ];
    }
}
