<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('google-sheets.spreadsheet_id');

        if (!$this->spreadsheetId) {
            return;
        }

        try {
            $this->client = new Client();
            $this->client->setAuthConfig(storage_path(config('google-sheets.credentials_path')));
            $this->client->addScope(Sheets::SPREADSHEETS);
            $this->client->setAccessType('offline');
            $this->service = new Sheets($this->client);
        } catch (\Exception $e) {
            Log::error('Google Sheets Init Error: ' . $e->getMessage());
            $this->service = null;
        }
    }

    public function isReady(): bool
    {
        return $this->service !== null && $this->spreadsheetId;
    }

    public function read(string $sheetName, string $range = 'A:Z')
    {
        if (!$this->isReady()) return [];

        try {
            $range = "'{$sheetName}'!{$range}";
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            return $response->getValues() ?? [];
        } catch (\Exception $e) {
            Log::error('Sheets Read Error [' . $sheetName . ']: ' . $e->getMessage());
            return [];
        }
    }

    public function write(string $sheetName, string $range, array $values, bool $raw = false)
    {
        if (!$this->isReady()) return false;

        try {
            $range = "'{$sheetName}'!{$range}";
            $body = new ValueRange(['values' => $values]);
            $params = ['valueInputOption' => $raw ? 'RAW' : 'USER_ENTERED'];
            $result = $this->service->spreadsheets_values->update(
                $this->spreadsheetId, $range, $body, $params
            );
            return $result->getUpdatedCells();
        } catch (\Exception $e) {
            Log::error('Sheets Write Error [' . $sheetName . ']: ' . $e->getMessage());
            return false;
        }
    }

    public function writeCell(string $sheetName, string $cell, $value)
    {
        return $this->write($sheetName, $cell, [[$value]]);
    }

    public function append(string $sheetName, array $rowData)
    {
        if (!$this->isReady()) return false;

        try {
            $range = "'{$sheetName}'!A:A";
            $body = new ValueRange(['values' => [$rowData]]);
            $params = [
                'valueInputOption' => 'USER_ENTERED',
                'insertDataOption' => 'INSERT_ROWS'
            ];
            $result = $this->service->spreadsheets_values->append(
                $this->spreadsheetId, $range, $body, $params
            );
            return $result->getUpdates()->getUpdatedCells();
        } catch (\Exception $e) {
            Log::error('Sheets Append Error [' . $sheetName . ']: ' . $e->getMessage());
            return false;
        }
    }

    public function batchUpdate(array $updates)
    {
        if (!$this->isReady()) return false;

        try {
            $data = [];
            foreach ($updates as $u) {
                $data[] = [
                    'range'  => "'{$u['sheet']}'!{$u['cell']}",
                    'values' => [[$u['value']]],
                ];
            }
            $body = new BatchUpdateValuesRequest([
                'valueInputOption' => 'USER_ENTERED',
                'data' => $data,
            ]);
            $this->service->spreadsheets_values->batchUpdate($this->spreadsheetId, $body);
            return true;
        } catch (\Exception $e) {
            Log::error('Sheets Batch Error: ' . $e->getMessage());
            return false;
        }
    }

    public function clear(string $sheetName, string $range)
    {
        if (!$this->isReady()) return false;

        try {
            $range = "'{$sheetName}'!{$range}";
            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId, $range,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Sheets Clear Error: ' . $e->getMessage());
            return false;
        }
    }

    public function colToLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col = (int)floor($col / 26);
        }
        return $letter;
    }

    public function getNextRow(string $sheetName, string $col = 'A')
    {
        $data = $this->read($sheetName, "{$col}2000");
        return count($data) + 1;
    }
}