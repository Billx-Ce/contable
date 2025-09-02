<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubigeo_com',  // Código completo de 6 dígitos
        'ubigeo_pro'   // Relación con provincia (4 dígitos)
    ];

    // ⚠️ Si no usas id autoincrement y tu PK real es el código:
    protected $primaryKey = 'ubigeo_com';
    public $incrementing = false;
    protected $keyType = 'string';

    // Relación: Un distrito pertenece a una provincia
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'ubigeo_pro', 'ubigeo_pro');
    }

    // Relación: Un distrito tiene muchas personas
    public function personas()
    {
        return $this->hasMany(Persona::class, 'ubigeo_com', 'ubigeo_com');
    }

    // Relación: Un distrito pertenece a un departamento (a través de provincia)
    public function departamento()
    {
        return $this->hasOneThrough(
            Departamento::class, // Modelo final
            Provincia::class,    // Modelo intermedio
            'ubigeo_pro',        // FK en Provincia que conecta con Distrito
            'ubigeo_dep',        // FK en Departamento que conecta con Provincia
            'ubigeo_pro',        // Local key en Distrito
            'ubigeo_dep'         // Local key en Provincia
        );
    }
}
