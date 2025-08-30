<?php

namespace App\Services;

use App\Models\SidlanResponse;
use Illuminate\Support\Facades\Artisan;

class SidlanLocalAPIServices
{
    public function executeRequest(array $params = [])
    {
        $defaults = [
            'dataset_id'   => 'ir-01-001',
            'return_values'=> 'json',
            'cluster'      => 'all',
            'region'       => 'all',
            'province'     => 'all',
            'group_status' => 'all',
        ];

        $params = array_merge($defaults, $params);

        $query = SidlanResponse::where('dataset_id', $params['dataset_id'])
            ->where('cluster', $params['cluster'])
            ->where('region', $params['region'])
            ->where('province', $params['province'])
            ->where('group_status', $params['group_status'])
            ->latest();


        $record = $query->first();

        if ($record && $record->data) {
            return $record->data;
        }

        // Fetch if missing
        Artisan::call('sidlan:fetch');

        $record = $query->first();
        return ($record && $record->data)
            ? $record->data
            : ['error' => 'No data available'];
    }
}
