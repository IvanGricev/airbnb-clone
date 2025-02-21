<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SoftDeleteOldChats;

Schedule::command('chats:soft-delete-old')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->emailOutputTo('admin@example.com');