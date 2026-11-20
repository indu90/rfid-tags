<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    protected $fillable = [
        'serial_no',
        'asset_code',
        'name',
        'location_id',
        'category',
        'status',
        'last_seen_at',
    ];
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function rfidTags(): HasMany
    {
        return $this->hasMany(RfidTag::class);
    }

    public function activeRfidTag(): HasOne
    {
        return $this->hasOne(RfidTag::class)->where('status', 'active');
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
