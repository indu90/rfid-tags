<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_code' => $this->asset_code,
            'name' => $this->name,
            'category' => $this->category,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'location' => $this->whenLoaded('location', fn() => [
                'id' => $this->location->id,
                'code' => $this->location->code,
                'name' => $this->location->name,
                'type' => $this->location->type,
            ], fn() => $this->location_id ? ['id' => $this->location_id] : null),
            'active_rfid_tag' => $this->whenLoaded(
                'activeRfidTag',
                fn() => new RfidTagResource($this->activeRfidTag)
            ),
            'rfid_tags' => RfidTagResource::collection($this->whenLoaded('rfidTags')),
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
