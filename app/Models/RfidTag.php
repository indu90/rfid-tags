<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RfidTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'reader_id',
        'antenna_no',
        'epc',
        'rssi',
        'raw_payload',
        'read_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'read_at' => 'datetime',
    ];
}
