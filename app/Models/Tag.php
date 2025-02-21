<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'category'];

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }
}
