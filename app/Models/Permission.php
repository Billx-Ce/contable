<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['nombre','descripcion','grupo'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
                    ->withTimestamps();
    }

    // Helpers
    public static function createCrudPermissions(string $resource, ?string $grupo = null): array
    {
        $grupo = $grupo ?? $resource;
        $actions = ['view','create','edit','delete'];
        $out = [];
        foreach ($actions as $a) {
            $out[] = self::firstOrCreate(
                ['nombre' => "{$resource}.{$a}"],
                ['descripcion' => ucfirst($a) . " {$resource}", 'grupo' => $grupo]
            );
        }
        return $out;
    }
}
