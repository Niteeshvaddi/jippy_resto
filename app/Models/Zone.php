<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zone';

    public $timestamps = false;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'latitude',
        'longitude',
        'area',
        'publish',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'area' => 'array',
        'publish' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('publish', true);
    }
}

