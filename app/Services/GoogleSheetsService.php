<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GoogleSheetsService
{
    protected $spreadsheetId;
    protected $accessToken;

    public function __construct()
    {
        $this->spreadsheetId = config('google-sheets.spreadsheet_id');
    }

    public function isReady(): bool
    {
        $credentialsPath = storage_path(config('google-sheets.credentials_path'));
        return $this->spreadsheetId && file_exists($credentialsPath);
    }

    public function read(string $sheetName, string $range = 'A:Z')
    {
        if (!$this->isReady()) return [];

        try {
            $range = "'{$sheetName}'!{$range}";
            $data = $this->apiCall('get', "/values/" . urlencode($range));
            return $data['values'] ?? [];
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
            $params = '?valueInputOption=' . ($raw ? 'RAW' : 'USER_ENTERED');
            $body = ['values' => $values];
            
            $data = $this->apiCall('put', "/values/" . urlencode($range) . $params, $body);
            return $data['updatedCells'] ?? false;
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
            $params = '?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS';
            $body = ['values' => [$rowData]];
            
            $data = $this->apiCall('post', "/values/" . urlencode($range) . ":append" . $params, $body);
            return $data['updates']['updatedCells'] ?? false;
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
                // ⛔ Safety: skip jika kolom melebihi Z (indeks 26)
                $cell = $u['cell'];
                $colLetter = preg_replace('/[0-9]/', '', $cell);
                if ($this->letterToCol($colLetter) > 26) {
                    Log::warning("Skip kolom {$colLetter} melebihi batas Z di sheet {$u['sheet']}");
                    continue;
                }

                $data[] = [
                    'range'  => "'{$u['sheet']}'!{$u['cell']}",
                    'values' => [[$u['value']]],
                ];
            }

            if (empty($data)) return false;

            $body = ['data' => $data];
            
            $result = $this->apiCall('post', '/values:batchUpdate?valueInputOption=USER_ENTERED', $body);
            return !is_null($result);
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
            $result = $this->apiCall('post', "/values/" . urlencode($range) . ":clear");
            return !is_null($result);
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

    public function letterToCol(string $letter): int
    {
        $col = 0;
        $length = strlen($letter);
        for ($i = 0; $i < $length; $i++) {
            $col = $col * 26 + (ord(strtoupper($letter[$i])) - 64);
        }
        return $col;
    }

    public function getNextRow(string $sheetName, string $col = 'A')
    {
        try {
            $data = $this->read($sheetName, "{$col}1:{$col}1000");
            $count = count($data);
            // Jika baris 1 berisi header, mulai dari baris 2
            return $count > 0 ? $count + 1 : 2;
        } catch (\Exception $e) {
            return 2;
        }
    }

    /* ═══════════════════════════════════════════
       INTERNAL: HTTP CLIENT & JWT AUTH
       ═══════════════════════════════════════════ */

    private function apiCall(string $method, string $uri, ?array $body = null)
    {
        $token = $this->getAccessToken();
        if (!$token) return null;

        $url = "https://sheets.googleapis.com/v4/spreadsheets/{$this->spreadsheetId}" . $uri;
        $http = Http::withToken($token)->timeout(60);

        if ($method === 'get') {
            $response = $http->get($url);
        } else {
            $response = $http->{$method}($url, $body);
        }

        if (!$response->successful()) {
            Log::error("Sheets API Error [{$method} {$uri}]: " . $response->body());
            return null;
        }

        return $response->json();
    }

    private function getAccessToken()
    {
        if (Cache::has('google_sheets_token')) {
            $this->accessToken = Cache::get('google_sheets_token');
            return $this->accessToken;
        }

        $credentialsPath = storage_path(config('google-sheets.credentials_path'));
        if (!file_exists($credentialsPath)) return null;

        $cred = json_decode(file_get_contents($credentialsPath), true);
        if (!$cred || !isset($cred['client_email'], $cred['private_key'])) {
            Log::error('Invalid Google Credentials format.');
            return null;
        }

        $now = time();
        $payload = [
            'iss'   => $cred['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];

        $jwt = $this->encodeJwt($payload, $cred['private_key']);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['access_token'];
            Cache::put('google_sheets_token', $token, 3500); 
            $this->accessToken = $token;
            return $token;
        }

        Log::error('Failed to get Google Access Token: ' . $response->body());
        return null;
    }

    private function encodeJwt(array $payload, string $privateKey): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));

        $signature = '';
        openssl_sign(
            $base64UrlHeader . '.' . $base64UrlPayload,
            $signature,
            $privateKey,
            OPENSSL_ALGO_SHA256
        );

        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

        private $sheetIdCache = [];

    public function getSheetId(string $sheetName)
    {
        if (isset($this->sheetIdCache[$sheetName])) {
            return $this->sheetIdCache[$sheetName];
        }

        $data = $this->apiCall('get', '?fields=sheets.properties');
        if (!$data || !isset($data['sheets'])) return 0;

        foreach ($data['sheets'] as $sheet) {
            if (isset($sheet['properties']['title']) && $sheet['properties']['title'] === $sheetName) {
                $this->sheetIdCache[$sheetName] = $sheet['properties']['sheetId'];
                return $this->sheetIdCache[$sheetName];
            }
        }
        Log::warning("Sheet '{$sheetName}' tidak ditemukan di spreadsheet");
        return null;
    }

    /**
     * Set dropdown validation di satu cell, mengambil opsi dari range A1 lain.
     * 
     * Contoh:
     *   setDataValidation('HASIL JURI', 'A2', "'HASIL JURI'!Z2:Z")
     *   → Cell A2 jadi dropdown yang opsinya isi kolom Z mulai baris 2 ke bawah.
     */
    public function setDataValidation(string $sheetName, string $cell, string $sourceRangeA1)
    {
        if (!$this->isReady()) return false;

        $sheetId = $this->getSheetId($sheetName);
        if ($sheetId === null) return false;

        // Parse "A2" → kolom & baris (0-based untuk Google API)
        if (!preg_match('/^([A-Z]+)(\d+)$/', strtoupper($cell), $m)) {
            Log::error("setDataValidation: format cell tidak valid: {$cell}");
            return false;
        }
        $colIdx = $this->letterToCol($m[1]) - 1;
        $rowIdx = (int) $m[2] - 1;

        $request = [
            'setDataValidation' => [
                'range' => [
                    'sheetId'          => $sheetId,
                    'startRowIndex'    => $rowIdx,
                    'endRowIndex'      => $rowIdx + 1,
                    'startColumnIndex' => $colIdx,
                    'endColumnIndex'   => $colIdx + 1,
                ],
                'rule' => [
                    'condition' => [
                        'type'   => 'ONE_OF_RANGE',
                        'values' => [
                            ['userEnteredValue' => '=' . $sourceRangeA1],
                        ],
                    ],
                    'showCustomUi' => true,
                    'strict'       => false,
                ],
            ],
        ];

        return $this->formatCells([$request]);
    }

    /**
     * Auto-detect separator function args sesuai locale spreadsheet.
     * Indonesia/EU pakai ';' (semicolon), US pakai ',' (comma).
     */
    public function getLocaleSeparator(): string
    {
        static $cached = null;
        if ($cached !== null) return $cached;

        if (!$this->isReady()) return ',';

        try {
            $data = $this->apiCall('get', '?fields=properties.locale');
            $locale = $data['properties']['locale'] ?? 'en_US';

            // Locales yang pakai koma sebagai argument separator
            $commaLocales = ['en_US', 'en_GB', 'en_CA', 'en_AU', 'en_NZ', 'ja_JP', 'ko_KR', 'zh_CN', 'zh_TW'];
            $cached = in_array($locale, $commaLocales) ? ',' : ';';

            Log::info("Sheet locale: {$locale}, separator: '{$cached}'");
            return $cached;
        } catch (\Exception $e) {
            Log::warning('getLocaleSeparator gagal: ' . $e->getMessage());
            $cached = ',';
            return $cached;
        }
    }

    public function formatCells(array $requests)
    {
        if (!$this->isReady() || empty($requests)) return false;

        try {
            $body = ['requests' => $requests];
            $result = $this->apiCall('post', ':batchUpdate', $body);
            return !is_null($result);
        } catch (\Exception $e) {
            Log::error('Sheets Format Error: ' . $e->getMessage());
            return false;
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}