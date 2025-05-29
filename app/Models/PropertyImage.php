<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $fillable = [
        'property_id',
        'image_data',
        'mime_type',
        'original_name',
        'size'
    ];

    protected $casts = [
        'image_data' => 'binary',
        'size' => 'integer'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function getImageUrlAttribute()
    {
        return route('property.image', $this->id);
    }
}
