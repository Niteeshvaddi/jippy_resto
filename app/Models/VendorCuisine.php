<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorCuisine extends Model
{
    protected $table = 'vendor_cuisines';

    public $timestamps = false;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'photo',
        'publish',
        'show_in_homepage',
        'image',
    ];

    protected $casts = [
        'publish' => 'boolean',
        'show_in_homepage' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('publish', true);
    }
}

