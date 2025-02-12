<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LandlordApplication extends Model
{
    protected $fillable = [
        'user_id',
        'message',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            try {
                if (!$application->status) {
                    $application->status = 'pending';
                }
            } catch (\Exception $e) {
                Log::error('Ошибка при создании заявки арендодателя', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                throw $e;
            }
        });
    }
}