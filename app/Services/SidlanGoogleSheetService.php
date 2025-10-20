<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SidlanGoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;


    public function __construct()
    {
        $this->initClient();
    }

    protected function initClient(): void
    {
        $this->spreadsheetId = config('app.google_sheet_id');

        $this->client = new Client();
        $this->client->setApplicationName('Laravel Sidlan Google Sheet Integration');
        $this->client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $this->client->setAuthConfig(storage_path('app/google/sidlan-i-reap-c5f44841b4cd.json'));
        $this->client->setAccessType('offline');

        $this->service = new Sheets($this->client);
    }

    private function columnIndexToLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $temp = ($index - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $index = (int)(($index - $temp - 1) / 26);
        }
        return $letter;
    }

    public function getSheetData(string $sheetName): array
    {
        if (empty($this->service) || empty($this->spreadsheetId)) {
            $this->initClient();
        }

        try {
            return Cache::remember("sheet_data_{$sheetName}", 300, function () use ($sheetName) {
                $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId, [
                    'ranges' => [$sheetName],
                    'fields' => 'sheets.properties.gridProperties'
                ]);

                $sheet = $spreadsheet->getSheets()[0] ?? null;
                if (!$sheet) {
                    throw new \Exception("Sheet '{$sheetName}' not found in spreadsheet.");
                }

                $gridProps = $sheet->getProperties()->getGridProperties();
                $rows = $gridProps->getRowCount() ?? 0;
                $cols = $gridProps->getColumnCount() ?? 0;
                $lastCol = $this->columnIndexToLetter($cols);
                $range = "{$sheetName}!A1:{$lastCol}{$rows}";

                $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
                $values = $response->getValues() ?? [];

                if (empty($values) || count($values) < 2) {
                    Log::warning("⚠️ Empty or incomplete data for {$sheetName}", ['rows' => count($values)]);
                    return [];
                }

                $headers = array_map('trim', $values[0]);
                $rowsData = array_slice($values, 1);
                $result = [];

                foreach ($rowsData as $row) {
                    $rowData = [];
                    foreach ($headers as $i => $header) {
                        $rowData[$header] = $row[$i] ?? null;
                    }
                    $result[] = $rowData;
                }

                return $result;
            });
        } catch (\Exception $e) {
            $cached = Cache::get("sheet_data_{$sheetName}", []);
            if (!empty($cached)) {
                Log::warning("⚠️ Using cached copy for {$sheetName}");
            }

            return $cached;
        }
    }

    public function getLatestLogTimestampDirect(string $sheetName = 'Logs'): ?string
    {
        $range = "{$sheetName}!A:C";

        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues() ?? [];

        if (empty($values) || count($values) < 2) {
            return null;
        }

        $rows = array_slice($values, 1);

        for ($i = count($rows) - 1; $i >= 0; $i--) {
            $row = $rows[$i];
            $timestamp = $row[0] ?? null;
            $user = $row[1] ?? null;
            $message = $row[2] ?? null;

            if ($user === 'SYSTEM' && str_contains($message, 'Hourly update completed successfully')) {
                return \Carbon\Carbon::parse($timestamp)->format('M d, Y h:i:s A');
            }
        }

        return null;
    }


    /**
     * Backward-compatible alias (to prevent 'undefined method executeRequest' errors)
     */
    public function executeRequest(array $params = []): array
    {
        $dataset = $params['dataset_id'] ?? 'ir-01-001';
        return $this->getSheetData($dataset);
    }
}
