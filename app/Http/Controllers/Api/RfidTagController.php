<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessRfidScanJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidTagController extends Controller
{
    /**
     * Store RFID Tag Scans transmitted from hardware client.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tags'              => 'required|array',
            'tags.*.epc'        => 'required|string',
            'tags.*.reader_id'  => 'nullable|string',
            'tags.*.antenna_no' => 'nullable|integer',
            'tags.*.rssi'       => 'nullable|integer',
        ]);

        foreach ($validated['tags'] as $tag) {
            // Dispatch asynchronously to keep endpoint response speed sub-50ms
            ProcessRfidScanJob::dispatch($tag);
        }

        return response()->json([
            'status'  => 'success',
            'message' => count($validated['tags']) . ' RFID tags queued for processing.',
        ], 200);
    }
}
