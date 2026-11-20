<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreRfidScanBatchRequest;
use App\Http\Resources\Api\RfidScanBatchResultResource;
use App\Models\Asset;
use App\Models\RfidScanLog;
use App\Models\RfidTag;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RfidScanIngestionController extends Controller
{
    private const DEDUP_WINDOW_SECONDS = 10;

    public function store(StoreRfidScanBatchRequest $request): RfidScanBatchResultResource
    {
        $validated = $request->validated();

        $readerId = $validated['reader_id'];
        $zoneId = $validated['zone_id'] ?? null;
        $clientType = $validated['client_type'] ?? 'scanner_client';

        $normalizedScans = [];
        foreach ($validated['scans'] as $scan) {
            $normalizedScans[] = [
                'epc' => $scan['epc'],
                'rssi' => $scan['rssi'] ?? null,
                'read_count' => $scan['read_count'] ?? 1,
                'scanned_at' => isset($scan['scanned_at'])
                    ? CarbonImmutable::parse($scan['scanned_at'])
                    : CarbonImmutable::now(),
            ];
        }

        $uniqueEpcs = array_values(array_unique(array_column($normalizedScans, 'epc')));

        $tagsByEpc = RfidTag::with('asset')
            ->whereIn('epc', $uniqueEpcs)
            ->where('status', 'active')
            ->get()
            ->keyBy('epc');

        $accepted = 0;
        $deduplicated = 0;
        $matchedAssets = [];
        $unmatchedEpcs = [];
        $logsToInsert = [];
        $seenAssetIds = [];
        $now = CarbonImmutable::now();

        foreach ($normalizedScans as $scan) {
            $epc = $scan['epc'];
            $dedupKey = "rfid_dedup:{$readerId}:{$epc}";

            if (! Cache::add($dedupKey, true, now()->addSeconds(self::DEDUP_WINDOW_SECONDS))) {
                $deduplicated++;
                continue;
            }

            $accepted++;
            $tag = $tagsByEpc->get($epc);
            $asset = $tag?->asset;
            $scannedAt = $scan['scanned_at'];

            $logsToInsert[] = [
                'rfid_tag_id' => $tag?->id,
                'asset_id' => $asset?->id,
                'location_id' => $asset?->location_id,
                'epc' => $epc,
                'reader_id' => $readerId,
                'zone_id' => $zoneId,
                'client_type' => $clientType,
                'rssi' => $scan['rssi'],
                'read_count' => $scan['read_count'],
                'scanned_at' => $scannedAt->toDateTimeString(),
                'created_at' => $now->toDateTimeString(),
            ];

            if ($asset) {
                $seenAssetIds[$asset->id] = $scannedAt;

                $matchedAssets[$epc] = [
                    'epc' => $epc,
                    'rfid_tag_id' => $tag->id,
                    'asset_id' => $asset->id,
                    'asset_code' => $asset->asset_code ?? null,
                    'asset_name' => $asset->name ?? null,
                    'zone_id' => $zoneId,
                    'scanned_at' => $scannedAt->toIso8601String(),
                ];
            } else {
                $unmatchedEpcs[$epc] = $epc;
            }
        }

        if ($logsToInsert !== [] || $seenAssetIds !== []) {
            DB::transaction(function () use ($logsToInsert, $seenAssetIds) {
                if ($logsToInsert !== []) {
                    RfidScanLog::insert($logsToInsert);
                }

                foreach ($seenAssetIds as $assetId => $lastSeenAt) {
                    Asset::whereKey($assetId)
                        ->where(function ($query) use ($lastSeenAt) {
                            $query->whereNull('last_seen_at')
                                ->orWhere('last_seen_at', '<=', $lastSeenAt->toDateTimeString());
                        })
                        ->update(['last_seen_at' => $lastSeenAt->toDateTimeString()]);
                }
            });
        }

        return new RfidScanBatchResultResource([
            'reader_id' => $readerId,
            'zone_id' => $zoneId,
            'received' => count($normalizedScans),
            'accepted' => $accepted,
            'deduplicated' => $deduplicated,
            'matched_assets' => $matchedAssets,
            'unmatched_or_unassigned_epcs' => $unmatchedEpcs,
        ]);
    }
}
