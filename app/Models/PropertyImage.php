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

    protected function setImageDataAttribute($value)
    {
        if (is_resource($value)) {
            $this->attributes['image_data'] = stream_get_contents($value);
        } else {
            $this->attributes['image_data'] = $value;
        }
    }

    protected function getImageDataAttribute($value)
    {
        return $value;
    }
}
