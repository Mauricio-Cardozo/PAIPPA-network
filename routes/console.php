<?php

use Illuminate\Support\Facades\Schedule;

// Borra a diario los registros vencidos de los modelos Prunable
// (los clics de WhatsApp de más de 12 meses, por la Ley 25.326).
Schedule::command('model:prune')->daily();
