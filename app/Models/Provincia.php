<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubigeo_pro',      // Código de 4 dígitos (2 dep + 2 prov)
        'ubigeo_dep'       // Relación con departamento (2 dígitos)
    ];
    
    // Relación: Una provincia pertenece a un departamento
    public function departamento()
{
    return $this->belongsTo(Departamento::class, 'ubigeo_dep', 'ubigeo_dep');
}


    // Relación: Una provincia tiene muchos distritos
    public function distritos()
    {
        return $this->hasMany(Distrito::class, 'ubigeo_pro', 'ubigeo_pro');
    }

    // Relación indirecta con personas a través de distritos
    public function personas()
    {
        return $this->hasManyThrough(
            Persona::class,
            Distrito::class,
            'ubigeo_pro',   // FK en Distrito
            'ubigeo_com',    // FK en Persona
            'ubigeo_pro',    // PK en Provincia
            'ubigeo_com'     // PK en Distrito
        );
    }
}
