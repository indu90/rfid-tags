<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'location_name',
        'location_type',
        'status',
    ];

    /**
     * Get the area that this location belongs to.
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get the devices associated with this location.
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
