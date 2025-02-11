<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * Пути, которые не должны попадать под режим обслуживания.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
