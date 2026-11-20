<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'serial_no',
        'asset_code',
        'name',
        'location_id',
        'status',
    ];
}
