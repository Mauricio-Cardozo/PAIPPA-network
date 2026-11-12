<?php

namespace App\Policies;

use App\Models\Conversacion;
use App\Models\User;

/**
 * Solo los dos participantes pueden leer y responder una conversación.
 * Laravel la encuentra sola por el nombre (Conversacion → ConversacionPolicy).
 */
class ConversacionPolicy
{
    public function view(User $user, Conversacion $conversacion): bool
    {
        return $conversacion->participa($user);
    }

    public function responder(User $user, Conversacion $conversacion): bool
    {
        return $conversacion->participa($user);
    }
}
