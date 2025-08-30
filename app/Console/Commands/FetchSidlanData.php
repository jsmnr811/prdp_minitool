<?php

namespace App\Console\Commands;

use App\Models\SidlanResponse;
use Illuminate\Console\Command;
use App\Services\SidlanAPIServices;

class FetchSidlanData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sidlan:fetch';

    /**
     * The console command description.
     */
    protected $description = 'Fetch data from Sidlan API and store into sidlan_responses table';

    /**
     * Execute the console command.
     */
    public function handle(SidlanAPIServices $sidlanService)
    {
        $datasets =
            [
                'ir-01-001',
                'ir-01-002',
                'ir-01-003a',
                'ir-01-003b',
            ];
        foreach ($datasets as $dataset) {

            $this->info('Fetching data from Sidlan API for dataset ' . $dataset . '...');

            $response = $sidlanService->executeRequest([
                'dataset_id' => $dataset,
            ]);

            if (isset($response['error'])) {
                $this->error('Error: ' . $response['error']);
                return Command::FAILURE;
            }

            SidlanResponse::updateOrCreate(
                [
                    'dataset_id'   => $dataset,
                    'cluster'      => 'all',
                    'region'       => 'all',
                    'province'     => 'all',
                    'group_status' => 'all',
                ],
                [
                    'data'         => $response, // JSON will be stored, casted as array

                ]

            );
        }
        // Save a single row containing params + data


        $this->info('Data stored successfully.');

        return Command::SUCCESS;
    }
}
