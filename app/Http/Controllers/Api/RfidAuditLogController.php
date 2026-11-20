<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\RfidAssignmentLogResource;
use App\Models\Asset;
use App\Models\RfidAssignmentLog;
use App\Models\RfidTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RfidAuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'epc' => ['nullable', 'string', 'max:64'],
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'rfid_tag_id' => ['nullable', 'integer', 'exists:rfid_tags,id'],
            'action' => ['nullable', 'string', 'in:assigned,unassigned,reassigned'],
            'reason' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = RfidAssignmentLog::query()
            ->with(['asset', 'rfidTag', 'actor'])
            ->latest('occurred_at');

        if (! empty($validated['epc'])) {
            $query->where('epc', strtoupper(trim($validated['epc'])));
        }

        if (! empty($validated['asset_id'])) {
            $query->where('asset_id', $validated['asset_id']);
        }

        if (! empty($validated['rfid_tag_id'])) {
            $query->where('rfid_tag_id', $validated['rfid_tag_id']);
        }

        if (! empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        if (! empty($validated['reason'])) {
            $query->where('reason', $validated['reason']);
        }

        if (! empty($validated['from'])) {
            $query->where('occurred_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->where('occurred_at', '<=', $validated['to']);
        }

        $logs = $query->paginate($validated['per_page'] ?? 25)->withQueryString();

        return RfidAssignmentLogResource::collection($logs);
    }

    public function byAsset(Request $request, Asset $asset): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'in:assigned,unassigned,reassigned'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $asset->assignmentLogs()
            ->with(['rfidTag', 'actor'])
            ->latest('occurred_at');

        if (! empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        $logs = $query->paginate($validated['per_page'] ?? 25)->withQueryString();

        return RfidAssignmentLogResource::collection($logs);
    }

    public function byTag(Request $request, RfidTag $rfidTag): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'action' => ['nullable', 'string', 'in:assigned,unassigned,reassigned'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $rfidTag->assignmentLogs()
            ->with(['asset', 'actor'])
            ->latest('occurred_at');

        if (! empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        $logs = $query->paginate($validated['per_page'] ?? 25)->withQueryString();

        return RfidAssignmentLogResource::collection($logs);
    }
}
