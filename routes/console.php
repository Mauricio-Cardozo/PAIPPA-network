<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// PAI-75: borra todos los días los registros vencidos (clics de WhatsApp de más de 12 meses).
Schedule::command('model:prune')->daily();
