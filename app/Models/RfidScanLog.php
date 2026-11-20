<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfidScanLog extends Model
{
    protected $table = 'rfid_scan_logs';

    protected $fillable = [
        'epc',
        'asset_id',
        'reader_id',
        'reader_name',
        'antenna_no',
        'rssi',
        'status',
        'location_id',
        'raw_payload',
        'read_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'read_at'     => 'datetime',
    ];
}
