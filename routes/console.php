<?php

declare(strict_types = 1);

use Illuminate\Support\Facades\Schedule;

if (class_exists('Laravel\Telescope\TelescopeServiceProvider')) {
    Schedule::command('telescope:prune')
        ->daily()
        ->onOneServer()
        ->withoutOverlapping();
}

Schedule::command('queue:prune-failed', ['--hours' => 48])->weekly();
