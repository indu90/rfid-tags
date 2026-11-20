<?php

namespace App\Services\Rfid;

use App\Models\Asset;
use App\Models\RfidScanLog;
use App\Models\RfidTag;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RfidScanProcessorService
{
    public function __construct(
        private readonly int $dedupWindowSeconds = 10
    ) {}

    /**
     * Process a validated scan batch payload and return a result summary array.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function processBatch(array $payload): array
    {
        $readerId = trim((string) $payload['reader_id']);
        $zoneId = isset($payload['zone_id']) && $payload['zone_id'] !== ''
            ? trim((string) $payload['zone_id'])
            : null;
        $clientType = isset($payload['client_type']) && $payload['client_type'] !== ''
            ? strtolower(trim((string) $payload['client_type']))
            : 'scanner_client';

        $normalizedScans = [];
        foreach ($payload['scans'] ?? [] as $scan) {
            $epc = strtoupper(trim((string) ($scan['epc'] ?? '')));
            if ($epc === '') {
                continue;
            }

            $normalizedScans[] = [
                'epc' => $epc,
                'rssi' => isset($scan['rssi']) ? (float) $scan['rssi'] : null,
                'read_count' => isset($scan['read_count']) ? (int) $scan['read_count'] : 1,
                'scanned_at' => isset($scan['scanned_at'])
                    ? CarbonImmutable::parse($scan['scanned_at'])
                    : CarbonImmutable::now(),
            ];
        }

        if ($normalizedScans === []) {
            return [
                'reader_id' => $readerId,
                'zone_id' => $zoneId,
                'received' => 0,
                'accepted' => 0,
                'deduplicated' => 0,
                'matched_assets' => [],
                'unmatched_or_unassigned_epcs' => [],
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
        $seenAssetLatestTimes = [];
        $now = CarbonImmutable::now();

        foreach ($normalizedScans as $scan) {
            $epc = $scan['epc'];
            $scannedAt = $scan['scanned_at'];
            $dedupKey = "rfid_dedup:{$readerId}:{$epc}";

            if (! Cache::add($dedupKey, true, $now->addSeconds($this->dedupWindowSeconds))) {
                $deduplicated++;
                continue;
            }

            $accepted++;
            $tag = $tagsByEpc->get($epc);
            $asset = $tag?->asset;

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

            if ($asset !== null) {
                if (
                    ! isset($seenAssetLatestTimes[$asset->id])
                    || $scannedAt->greaterThan($seenAssetLatestTimes[$asset->id])
                ) {
                    $seenAssetLatestTimes[$asset->id] = $scannedAt;
                }

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

        if ($logsToInsert !== [] || $seenAssetLatestTimes !== []) {
            DB::transaction(function () use ($logsToInsert, $seenAssetLatestTimes): void {
                if ($logsToInsert !== []) {
                    RfidScanLog::insert($logsToInsert);
                }

                foreach ($seenAssetLatestTimes as $assetId => $lastSeenAt) {
                    Asset::whereKey($assetId)
                        ->where(function ($query) use ($lastSeenAt): void {
                            $query->whereNull('last_seen_at')
                                ->orWhere('last_seen_at', '<=', $lastSeenAt->toDateTimeString());
                        })
                        ->update(['last_seen_at' => $lastSeenAt->toDateTimeString()]);
                }
            });
        }

        return [
            'reader_id' => $readerId,
            'zone_id' => $zoneId,
            'received' => count($normalizedScans),
            'accepted' => $accepted,
            'deduplicated' => $deduplicated,
            'matched_assets' => array_values($matchedAssets),
            'unmatched_or_unassigned_epcs' => array_values($unmatchedEpcs),
        ];
    }
}
