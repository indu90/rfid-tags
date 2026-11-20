<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfidAssignmentLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'epc' => $this->epc,
            'action' => $this->action,
            'reason' => $this->reason,
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'asset' => $this->whenLoaded(
                'asset',
                fn() => [
                    'id' => $this->asset->id,
                    'asset_code' => $this->asset->asset_code,
                    'name' => $this->asset->name,
                ],
                fn() => ['id' => $this->asset_id]
            ),
            'rfid_tag' => $this->whenLoaded(
                'rfidTag',
                fn() => [
                    'id' => $this->rfidTag->id,
                    'epc' => $this->rfidTag->epc,
                    'status' => $this->rfidTag->status,
                ],
                fn() => ['id' => $this->rfid_tag_id]
            ),
            'actor' => $this->whenLoaded('actor', function () {
                if (! $this->actor) {
                    return null;
                }

                return [
                    'type' => $this->actor_type,
                    'id' => $this->actor->getKey(),
                    'name' => $this->actor->name ?? null,
                    'email' => $this->actor->email ?? null,
                ];
            }),
        ];
    }
}
