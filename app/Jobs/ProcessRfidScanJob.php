<?php

namespace App\Jobs;

use App\Models\RfidTag;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ProcessRfidScanJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $tagData) {}

    public function handle(): void
    {
        $epc = trim((string) ($this->tagData['epc'] ?? ''));
        if ($epc === '') {
            return;
        }

        $readAt = now();
        if (!empty($this->tagData['read_at'])) {
            try {
                $readAt = Carbon::parse($this->tagData['read_at']);
            } catch (\Throwable) {
                $readAt = now();
            }
        }

        RfidTag::create([
            'reader_id'   => $this->tagData['reader_id'] ?? 'unknown',
            'antenna_no'  => $this->tagData['antenna_no'] ?? 1,
            'epc'         => $epc,
            'rssi'        => $this->tagData['rssi'] ?? null,
            'raw_payload' => $this->tagData,
            'read_at'     => $readAt,
        ]);

        $existingAsset = DB::table('assets')->where('serial_no', $epc)->first();

        $nextStatus = 'IN';
        if ($existingAsset) {
            $nextStatus = strtoupper((string) $existingAsset->status) === 'IN' ? 'OUT' : 'IN';
        }

        $locationId = $this->tagData['location_id'] ?? null;
        $now = now();

        if ($existingAsset) {
            $update = [
                'status'     => $nextStatus,
                'updated_at' => $now,
            ];

            if (!is_null($locationId)) {
                $update['location_id'] = $locationId;
            }

            DB::table('assets')
                ->where('id', $existingAsset->id)
                ->update($update);
        } else {
            try {
                DB::table('assets')->insert([
                    'serial_no'   => $epc,
                    'asset_code'  => 'AST-' . strtoupper(substr(md5($epc), 0, 8)),
                    'name'        => 'Asset ' . substr($epc, -6),
                    'location_id' => $locationId,
                    'status'      => $nextStatus,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            } catch (QueryException $e) {
                // If another worker inserted this EPC concurrently, update its status instead
                if (str_contains($e->getMessage(), 'Duplicate') || $e->getCode() === '23000') {
                    DB::table('assets')
                        ->where('serial_no', $epc)
                        ->update([
                            'status'     => $nextStatus,
                            'updated_at' => $now,
                        ]);
                } else {
                    throw $e;
                }
            }
        }
    }
}
