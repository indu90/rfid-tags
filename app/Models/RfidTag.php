<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'reader_id',
        'antenna_no',
        'epc',
        'rssi',
        'raw_payload',
        'asset_id',
        'status',
        'read_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'read_at' => 'datetime',
    ];
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(RfidAssignmentLog::class)->latest('occurred_at');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(RfidScanLog::class)->latest('scanned_at');
    }
}
