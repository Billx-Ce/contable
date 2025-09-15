<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        try {
            // 1) Limpiar pivotes + tablas base para “resetear”
            Schema::disableForeignKeyConstraints();

            DB::table('role_permissions')->truncate();
            DB::table('user_roles')->truncate();
            DB::table('permissions')->truncate();
            DB::table('roles')->truncate();

            Schema::enableForeignKeyConstraints();

            // 2) Crear permisos mínimos (solo los que usaremos)
            $perms = collect([
                // Personas
                ['nombre' => 'personas.view',   'descripcion' => 'Ver personas',     'grupo' => 'personas'],
                ['nombre' => 'personas.create', 'descripcion' => 'Crear personas',   'grupo' => 'personas'],
                ['nombre' => 'personas.edit',   'descripcion' => 'Editar personas',  'grupo' => 'personas'],
                ['nombre' => 'personas.delete', 'descripcion' => 'Eliminar personas','grupo' => 'personas'],

                // Usuarios (solo listado/vista)
                ['nombre' => 'usuarios.view',   'descripcion' => 'Ver usuarios',     'grupo' => 'usuarios'],

                // Administración (solo superadmin)
                ['nombre' => 'roles.view',      'descripcion' => 'Ver roles',        'grupo' => 'admin'],
                ['nombre' => 'roles.manage',    'descripcion' => 'Gestionar roles',  'grupo' => 'admin'],
                ['nombre' => 'permisos.view',   'descripcion' => 'Ver permisos',     'grupo' => 'admin'],
                ['nombre' => 'permisos.manage', 'descripcion' => 'Gestionar permisos','grupo' => 'admin'],
            ])->map(fn ($p) => Permission::create($p));

            // Helper para obtener IDs por nombre
            $id = function (string $permNombre) {
                return Permission::where('nombre', $permNombre)->value('id');
            };

            // 3) Crear roles
            $rSuper  = Role::create(['nombre' => 'superadmin',   'descripcion' => 'Super administrador con todos los privilegios', 'is_protected' => true]);
            $rAdmin  = Role::create(['nombre' => 'admin',         'descripcion' => 'Administrador (personas + usuarios)',          'is_protected' => false]);
            $rAdm    = Role::create(['nombre' => 'administrador', 'descripcion' => 'Administración solo de personas',              'is_protected' => false]);
            $rCliente= Role::create(['nombre' => 'cliente',       'descripcion' => 'Cliente (solo lectura de personas)',           'is_protected' => false]);

            // 4) Asignar permisos por rol

            // superadmin -> TODOS
            $rSuper->permissions()->sync(Permission::pluck('id')->all());

            // admin -> Personas (CRUD completo) + Usuarios (ver)
            $rAdmin->permissions()->sync([
                $id('personas.view'), $id('personas.create'), $id('personas.edit'), $id('personas.delete'),
                $id('usuarios.view'),
            ]);

            // administrador -> Personas (ver, crear, editar) SIN eliminar
            $rAdm->permissions()->sync([
                $id('personas.view'), $id('personas.create'), $id('personas.edit'),
            ]);

            // cliente -> solo ver personas
            $rCliente->permissions()->sync([
                $id('personas.view'),
            ]);

            // 5) Hacer superadmin al usuario anderlin@gmail.com (si existe)
            if ($u = User::where('email','anderlin@gmail.com')->first()) {
                $u->roles()->syncWithoutDetaching([$rSuper->id]);
                // si además usas el flag is_admin para el menú, lo ponemos en true:
                if (isset($u->is_admin) && $u->is_admin !== true) {
                    $u->is_admin = true;
                    $u->save();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
