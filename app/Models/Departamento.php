<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubigeo_dep'
    ];

    // Relación: Un departamento tiene muchas provincias
    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'ubigeo_dep', 'ubigeo_dep');
    }

    // Relación: Un departamento tiene muchas personas (a través de provincias y distritos)
    public function personas()
    {
        return $this->hasManyThrough(
            Persona::class,
            Distrito::class,
            'ubigeo_dep',
            'ubigeo_com',
            'ubigeo_dep',
            'ubigeo_com'
        );
    }
}
