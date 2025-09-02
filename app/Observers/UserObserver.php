<?php

// app/Observers/UserObserver.php
namespace App\Observers;

use App\Models\User;
use App\Models\Persona;

class UserObserver
{
    public function created(User $user): void
    {
        if ($user->persona_id) return;

        $persona = Persona::create([
            'nombre' => $user->name,
        ]);

        $user->persona()->associate($persona);
        $user->save();
    }

    // Opcional: sincronizar nombre si cambia
    public function updated(User $user): void
    {
        if ($user->wasChanged('name') && $user->persona) {
            $user->persona->update(['nombre' => $user->name]);
        }
    }
}

