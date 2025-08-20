<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ---- SCHEDULING ----
// Programar la sincronización automática (opcional)
//Schedule::command('sync:odoo-invoices')->hourly();

// O ejecutarlo diariamente a las 2:00 AM
Schedule::command('sync:incoming-odoo-invoices')->dailyAt('02:00');
Schedule::command('sync:outgoing-odoo-invoices')->dailyAt('02:05');

// O cada 30 minutos
// Schedule::command('sync:odoo-invoices')->everyThirtyMinutes();

// O cada minuto
// Schedule::command('sync:odoo-invoices')->everyMinute();