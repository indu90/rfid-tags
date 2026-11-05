<?php

namespace App\Jobs;

use App\Models\RfidTag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRfidScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $tagData) {}

    public function handle(): void
    {
        // Batch insert or handle duplication logic based on your business rules
        RfidTag::create([
            'reader_id'   => $this->tagData['reader_id'] ?? 'unknown',
            'antenna_no'  => $this->tagData['antenna_no'] ?? 1,
            'epc'         => $this->tagData['epc'],
            'rssi'        => $this->tagData['rssi'] ?? null,
            'raw_payload' => $this->tagData,
            'read_at'     => $this->tagData['read_at'] ?? now(),
        ]);

        // Optional: Trigger ERP events (e.g., Inventory movement, Order Booking update)
    }
}
