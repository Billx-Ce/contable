<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserAdmin extends Command
{
    /**
     * Uso:
     *  php artisan user:admin correo@dominio.com
     *  php artisan user:admin correo@dominio.com --revoke
     *  php artisan user:admin --id=44
     *  php artisan user:admin --id=44 --revoke
     */
    protected $signature = 'user:admin 
                            {email? : Email del usuario} 
                            {--id= : ID del usuario} 
                            {--revoke : Revoca el rol de administrador}';

    protected $description = 'Concede o revoca el rol de administrador a un usuario';

    public function handle(): int
    {
        $email  = $this->argument('email');
        $id     = $this->option('id');
        $revoke = (bool) $this->option('revoke');

        if (!$email && !$id) {
            $this->error('Debes proporcionar --id o el email del usuario.');
            return self::INVALID;
        }

        $query = User::query();
        if ($id)    $query->where('id', $id);
        if ($email) $query->where('email', $email);

        $user = $query->first();

        if (! $user) {
            $this->error('Usuario no encontrado.');
            return self::FAILURE;
        }

        if ($revoke && $user->is_admin === false) {
            $this->warn("El usuario {$user->email} ya NO es admin.");
            return self::SUCCESS;
        }
        if (!$revoke && $user->is_admin === true) {
            $this->warn("El usuario {$user->email} ya es admin.");
            return self::SUCCESS;
        }

        $user->is_admin = ! $revoke;
        $user->save();

        $msg = $revoke ? 'Admin revocado' : 'Admin concedido';
        $this->info("{$msg} a {$user->email} (id: {$user->id}).");

        return self::SUCCESS;
    }
}
