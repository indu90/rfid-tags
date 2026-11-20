<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidAssignmentLog extends Model
{
    protected $table = 'rfid_assignment_logs';

    protected $fillable = [
        'asset_id',
        'serial_no',
        'previous_serial_no',
        'action',
        'assigned_by',
        'remarks',
        'assigned_at',
    ];

    protected $casts = [
        'asset_id'    => 'integer',
        'assigned_by' => 'integer',
        'assigned_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
