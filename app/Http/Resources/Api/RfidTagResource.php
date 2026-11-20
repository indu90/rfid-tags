<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfidTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'epc' => $this->epc,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'asset' => $this->whenLoaded(
                'asset',
                fn() => new AssetResource($this->asset),
                fn() => $this->asset_id ? ['id' => $this->asset_id] : null
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
