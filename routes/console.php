<?php

use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('inventory:release-expired', function (InventoryService $inventory) {
    $count = $inventory->releaseExpiredReservations();
    $this->info("Released {$count} expired stock reservation(s).");
})->purpose('Release expired inventory reservations');

Schedule::command('inventory:release-expired')->everyFiveMinutes();
