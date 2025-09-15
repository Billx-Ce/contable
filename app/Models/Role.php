<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','descripcion','is_protected'];
    protected $casts = ['is_protected' => 'boolean'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
                    ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
                    ->withTimestamps();
    }

    public function syncPermissions(array $permissions): void
    {
        $ids = [];
        foreach ($permissions as $p) {
            if ($p instanceof Permission) $ids[] = $p->id;
            elseif (is_numeric($p))      $ids[] = (int)$p;
            elseif (is_string($p)) {
                $m = Permission::where('nombre', $p)->first();
                if ($m) $ids[] = $m->id;
            }
        }
        $this->permissions()->sync($ids);
    }
}
