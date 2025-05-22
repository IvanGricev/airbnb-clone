<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('min_stay', function ($attribute, $value, $parameters, $validator) {
            $startDate = Carbon::parse($validator->getData()[$parameters[0]]);
            $endDate = Carbon::parse($value);
            return $endDate->diffInDays($startDate) >= 1;
        }, 'Минимальный срок бронирования - 1 день.');
    }
}
