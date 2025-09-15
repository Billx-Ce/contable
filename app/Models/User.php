<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'persona_id',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    // ==================== ROLES Y PERMISOS ====================
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
                    ->withTimestamps();
    }

    /** Asigna un rol por id o por nombre */
    public function assignRole($role)
    {
        if (! $role instanceof Role) {
            $role = Role::where('nombre', $role)->firstOrFail();
        }
        return $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /** ¿Tiene alguno de estos roles? */
    public function hasRole($roles): bool
    {
        $roles = (array)$roles;
        return $this->roles()->whereIn('nombre', $roles)->exists();
    }

    /** ¿Tiene el permiso (vía alguno de sus roles)? */
    public function hasPermission(string $permNombre): bool
    {
        // Si tiene rol superadmin => todo permitido
        if ($this->roles()->where('nombre', 'superadmin')->exists()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', fn($q) =>
            $q->where('nombre', $permNombre)
        )->exists();
    }

    // ==================== MÉTODOS ADICIONALES ====================
    /** ¿Tiene todos los roles especificados? */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('nombre', $roles)->count() === count($roles);
    }

    /** ¿Tiene alguno de estos permisos? */
    public function hasAnyPermission(array $permissions): bool
    {
        // Si es admin, tiene todos los permisos
        if ($this->is_admin) {
            return true;
        }
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('nombre', $permissions);
            })->exists();
    }

    /** Remover un rol del usuario */
    public function removeRole($role): void
    {
        if (! $role instanceof Role) {
            $role = Role::where('nombre', $role)->firstOrFail();
        }
        $this->roles()->detach($role->id);
    }

    /** Sincronizar roles (reemplaza todos los roles actuales) */
    public function syncRoles(array $roles): void
    {
        $roleIds = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $roleModel = Role::where('nombre', $role)->first();
                if ($roleModel) {
                    $roleIds[] = $roleModel->id;
                }
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            } elseif (is_numeric($role)) {
                $roleIds[] = $role;
            }
        }

        $this->roles()->sync($roleIds);
    }

    /** Obtener todos los permisos del usuario a través de sus roles */
    public function getAllPermissions()
    {
        return Permission::whereHas('roles', function ($query) {
            $query->whereIn('roles.id', $this->roles->pluck('id'));
        })->get();
    }

    /** Obtener nombres de roles del usuario */
    public function getRoleNames(): array
    {
        return $this->roles->pluck('nombre')->toArray();
    }

    /** Obtener nombres de permisos del usuario */
    public function getPermissionNames(): array
    {
        return $this->getAllPermissions()->pluck('nombre')->toArray();
    }

    /** Verificar múltiples permisos (compatible con Laravel Gate) */
    public function can($abilities, $arguments = []): bool
    {
        if (is_array($abilities)) {
            return $this->hasAnyPermission($abilities);
        }
        return $this->hasPermission($abilities);
    }

    /** Verificar si NO tiene permiso */
    public function cannot($abilities, $arguments = []): bool
    {
        return !$this->can($abilities, $arguments);
    }

    /** Verificar si el usuario es super admin */
    public function isSuperAdmin(): bool
    {
        return $this->is_admin;
    }

    /** Verificar si puede asignar roles */
    public function canAssignRole($role): bool
    {
        // Los super admins pueden asignar cualquier rol
        if ($this->is_admin) {
            return true;
        }
        // Verificar si tiene permiso específico para asignar roles
        if (!$this->hasPermission('roles.assign')) {
            return false;
        }
        // Obtener el rol si es string
        if (is_string($role)) {
            $role = Role::where('nombre', $role)->first();
        }
        // No puede asignar roles protegidos si no es admin
        if ($role && $role->is_protected) {
            return false;
        }
        return true;
    }

    // ==================== SCOPES ====================
    /** Scope para obtener usuarios con un rol específico */
    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('nombre', $role);
        });
    }

    /** Scope para obtener usuarios con un permiso específico */
    public function scopeWithPermission($query, $permission)
    {
        return $query->whereHas('roles.permissions', function ($q) use ($permission) {
            $q->where('nombre', $permission);
        });
    }

    /** Scope para obtener usuarios admin */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /** Scope para obtener usuarios no admin */
    public function scopeNonAdmins($query)
    {
        return $query->where('is_admin', false);
    }
}
