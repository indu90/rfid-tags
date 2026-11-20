<?php

namespace App\Services\Rfid;

use App\Models\Asset;
use App\Models\RfidAssignmentLog;
use App\Models\RfidTag;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RfidAssignmentService
{
    /**
     * Assign an existing RFID tag (by EPC) to an asset inside a locked transaction.
     *
     * @throws HttpException
     */
    public function assign(
        Asset $asset,
        string $rawEpc,
        ?Authenticatable $actor = null,
        ?string $reason = 'initial_mapping',
        array $metadata = []
    ): RfidTag {
        $epc = strtoupper(trim($rawEpc));

        return DB::transaction(function () use ($asset, $epc, $actor, $reason, $metadata): RfidTag {
            $lockedAsset = Asset::whereKey($asset->id)->lockForUpdate()->firstOrFail();

            $tag = RfidTag::where('epc', $epc)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($tag->status, ['damaged', 'lost', 'retired'], true)) {
                abort(422, "Cannot assign a {$tag->status} RFID tag.");
            }

            if ($tag->asset_id !== null && (int) $tag->asset_id !== (int) $lockedAsset->id) {
                abort(409, 'This RFID tag is already assigned to another asset. Unassign it first.');
            }

            // If this asset already has a different active tag, unassign and log it first
            $previousTag = $lockedAsset->rfidTags()
                ->where('status', 'active')
                ->where('id', '!=', $tag->id)
                ->lockForUpdate()
                ->first();

            if ($previousTag !== null) {
                $previousTag->update([
                    'asset_id' => null,
                    'status' => 'unassigned',
                    'assigned_at' => null,
                ]);

                $this->recordLog(
                    tag: $previousTag,
                    asset: $lockedAsset,
                    action: 'unassigned',
                    actor: $actor,
                    reason: $reason ?: 'replaced_by_new_tag',
                    metadata: array_merge($metadata, [
                        'replaced_by_epc' => $tag->epc,
                        'replaced_by_rfid_tag_id' => $tag->id,
                    ])
                );
            }

            $hadPriorHistory = RfidAssignmentLog::where('rfid_tag_id', $tag->id)->exists();
            $isReassign = $hadPriorHistory || $tag->assigned_at !== null;
            $now = now();

            $tag->update([
                'asset_id' => $lockedAsset->id,
                'status' => 'active',
                'assigned_at' => $now,
            ]);

            $this->recordLog(
                tag: $tag,
                asset: $lockedAsset,
                action: $isReassign ? 'reassigned' : 'assigned',
                actor: $actor,
                reason: $reason ?: ($isReassign ? 'reassignment' : 'initial_mapping'),
                metadata: $metadata
            );

            return $tag->fresh('asset');
        });
    }

    /**
     * Detach an RFID tag from its current asset and record an audit log entry.
     *
     * @throws HttpException
     */
    public function unassign(
        RfidTag $tag,
        ?Authenticatable $actor = null,
        string $nextStatus = 'unassigned',
        ?string $reason = null,
        array $metadata = []
    ): RfidTag {
        if (! in_array($nextStatus, ['unassigned', 'damaged', 'lost', 'retired'], true)) {
            abort(422, 'Invalid post-unassignment tag status.');
        }

        return DB::transaction(function () use ($tag, $actor, $nextStatus, $reason, $metadata): RfidTag {
            $lockedTag = RfidTag::whereKey($tag->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTag->asset_id === null) {
                abort(422, 'This RFID tag is not currently assigned to an asset.');
            }

            $asset = Asset::whereKey($lockedTag->asset_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedTag->update([
                'asset_id' => null,
                'status' => $nextStatus,
                'assigned_at' => null,
            ]);

            $this->recordLog(
                tag: $lockedTag,
                asset: $asset,
                action: 'unassigned',
                actor: $actor,
                reason: $reason ?: $nextStatus,
                metadata: $metadata
            );

            return $lockedTag->fresh();
        });
    }

    private function recordLog(
        RfidTag $tag,
        Asset $asset,
        string $action,
        ?Authenticatable $actor,
        ?string $reason,
        array $metadata
    ): void {
        RfidAssignmentLog::create([
            'rfid_tag_id' => $tag->id,
            'asset_id' => $asset->id,
            'epc' => $tag->epc,
            'action' => $action,
            'reason' => $reason,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getAuthIdentifier(),
            'metadata' => $metadata === [] ? null : $metadata,
            'occurred_at' => now(),
        ]);
    }
}
