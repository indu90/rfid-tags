<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfidScanBatchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $matchedAssets = collect($this->resource['matched_assets'] ?? [])
            ->map(fn(array $item) => [
                'epc' => $item['epc'],
                'rfid_tag_id' => $item['rfid_tag_id'],
                'asset_id' => $item['asset_id'],
                'asset_code' => $item['asset_code'] ?? null,
                'asset_name' => $item['asset_name'] ?? null,
                'zone_id' => $item['zone_id'] ?? null,
                'scanned_at' => $item['scanned_at'] ?? null,
            ])
            ->values()
            ->all();

        $unmatchedEpcs = array_values($this->resource['unmatched_or_unassigned_epcs'] ?? []);

        return [
            'reader_id' => $this->resource['reader_id'],
            'zone_id' => $this->resource['zone_id'] ?? null,
            'summary' => [
                'received' => $this->resource['received'] ?? 0,
                'accepted' => $this->resource['accepted'] ?? 0,
                'deduplicated' => $this->resource['deduplicated'] ?? 0,
                'matched_count' => count($matchedAssets),
                'unmatched_count' => count($unmatchedEpcs),
            ],
            'matched_assets' => $matchedAssets,
            'unmatched_or_unassigned_epcs' => $unmatchedEpcs,
        ];
    }
}
