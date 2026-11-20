<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignRfidTagRequest;
use App\Http\Requests\Api\UnassignRfidTagRequest;
use App\Http\Resources\Api\RfidTagResource;
use App\Models\Asset;
use App\Models\RfidTag;
use App\Services\Rfid\RfidAssignmentService;
use Illuminate\Http\JsonResponse;

class AssetRfidAssignmentController extends Controller
{
    public function __construct(
        private readonly RfidAssignmentService $assignmentService
    ) {}

    public function assign(AssignRfidTagRequest $request, Asset $asset): JsonResponse
    {
        $data = $request->validated();

        $tag = $this->assignmentService->assign(
            asset: $asset,
            rawEpc: $data['epc'],
            actor: $request->user(),
            reason: $data['reason'] ?? 'initial_mapping',
            metadata: $data['metadata'] ?? []
        );

        return (new RfidTagResource($tag->load('asset')))
            ->additional(['message' => 'RFID tag assigned to asset.'])
            ->response();
    }

    public function unassign(UnassignRfidTagRequest $request, RfidTag $rfidTag): JsonResponse
    {
        $data = $request->validated();

        $tag = $this->assignmentService->unassign(
            tag: $rfidTag,
            actor: $request->user(),
            nextStatus: $data['status'] ?? 'unassigned',
            reason: $data['reason'] ?? null,
            metadata: $data['metadata'] ?? []
        );

        return (new RfidTagResource($tag))
            ->additional(['message' => 'RFID tag unassigned.'])
            ->response();
    }
}
