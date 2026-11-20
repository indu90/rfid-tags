<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRfidScanJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RfidTagController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        set_time_limit(120);

        $url = 'https://wavelinx.in/api/log.txt';

        try {
            $response = Http::connectTimeout(30)
                ->timeout(120)
                ->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to fetch data. HTTP status: ' . $response->status(),
                ], 502);
            }

            // Get raw text because log.txt has multiple blocks and separator lines[cite: 3]
            $rawContent = $response->body();

            // Split into individual log entries[cite: 3]
            $blocks = preg_split('/^=+\s*$/m', $rawContent);
            $jobs = [];

            foreach ($blocks as $block) {
                $block = trim($block);
                if ($block === '') {
                    continue;
                }

                // Remove leading "API Request" header if present before the JSON object[cite: 3]
                $jsonStart = strpos($block, '{');
                if ($jsonStart === false) {
                    continue;
                }

                $jsonString = trim(substr($block, $jsonStart));
                $entry = json_decode($jsonString, true);

                if (json_last_error() !== JSON_ERROR_NONE || !is_array($entry)) {
                    continue;
                }

                $items = $entry['json_body'] ?? null;

                if (is_string($items)) {
                    $items = json_decode($items, true);
                }

                if (!is_array($items)) {
                    continue;
                }

                $normalized = array_is_list($items) ? $items : [$items];

                foreach ($normalized as $item) {
                    if (is_array($item) && isset($item['epc'])) {
                        $jobs[] = new ProcessRfidScanJob([
                            'reader_id'   => $item['readerId'] ?? 'unknown',
                            'reader_name' => $item['readerName'] ?? null,
                            'epc'         => $item['epc'],
                            'antenna_no'  => $item['antenna'] ?? 1,
                            'rssi'        => $item['rssi'] ?? null,
                            'read_at'     => $item['timestamp'] ?? now(),
                        ]);
                    }
                }
            }

            foreach (array_chunk($jobs, 100) as $jobChunk) {
                Bus::batch($jobChunk)->dispatch();
            }
            return response()->json([
                'status'  => 'success',
                'message' => count($jobs) . ' RFID tags queued successfully.',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('RFID fetch/queue error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
