<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Persona extends Model
{
    use HasFactory;
    
    //relacion de persona a usuario
    protected $fillable = [
        'nombre',
        'pri_ape',
        'seg_ape',
        'fecha_nac',
        'telefono',
        'direccion',
        'tipo_doc',
        'num_doc',
        'ubigeo_com',
        'sexo',
        'discapacidad_id'
    ];

    protected $casts = [
        'fecha_nac' => 'date',
    ];

    protected $appends = [
        'nombre_completo',
        'fecha_y_edad',
        'ubicacion_completa',
        'tipo_documento_completo',
        'edad',
        'departamento_nombre',   // <— agregar
        'provincia_nombre',      // <— agregar
        'distrito_nombre',
        'discapacidad_nombre',       // <— agregar
    ];

    // 1 a 1: una persona tiene un usuario
    public function user()
    {
        return $this->hasOne(User::class, 'persona_id'); // FK: users.persona_id
    }
    /* ======================
       RELACIONES
    ====================== */

    public function discapacidad()
    {
        return $this->belongsTo(Discapacidad::class);
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'ubigeo_com', 'ubigeo_com');
    }

    /* ======================
       ACCESORS & MUTATORS
    ====================== */
    public function getDepartamentoNombreAttribute()
    {
    return $this->distrito?->provincia?->departamento?->nombre;
    }

    public function getProvinciaNombreAttribute()
    {
        return $this->distrito?->provincia?->nombre;
    }

    public function getDistritoNombreAttribute()
    {
        return $this->distrito?->nombre;
    }


    public function getNombreCompletoAttribute()
    {
        return trim(sprintf('%s %s %s', 
            $this->nombre,
            $this->pri_ape,
            $this->seg_ape ?? ''
        ));
    }

    public function getUbicacionCompletaAttribute()
    {
        if (!$this->distrito) return 'Sin ubicación';
        
        return implode(', ', array_filter([
            optional($this->distrito->provincia->departamento)->nombre,
            optional($this->distrito->provincia)->nombre,
            $this->distrito->nombre
        ]));
    }
    

    public function getTipoDocumentoCompletoAttribute()
    {
        $tipos = [
            'DNI' => 'Documento Nacional de Identidad',
            'CE' => 'Carné de Extranjería',
            'Pasaporte' => 'Pasaporte'
        ];
        return $tipos[$this->tipo_doc] ?? $this->tipo_doc;
    }

    public function getEdadAttribute()
    {
        return $this->fecha_nac?->age;
    }

    public function getFechaYEdadAttribute()
    {
        return $this->fecha_nac 
            ? $this->fecha_nac->format('d/m/Y') . ' (' . $this->edad . ' años)'
            : null;
    }

    

    protected function telefono(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? preg_replace('/[^0-9]/', '', $value) : null,
        );
    }

    /* ======================
       SCOPES
    ====================== */

    public function scopePorNombreCompleto($query, $busqueda)
    {
        return $query->whereRaw("
            CONCAT(
                nombre, ' ', 
                pri_ape, ' ', 
                COALESCE(seg_ape, '')
            ) LIKE ?", ["%{$busqueda}%"]);
    }

    public function scopePorDocumento($query, $numero)
    {
        return $query->where('num_doc', preg_replace('/[^0-9]/', '', $numero));
    }

    /* ======================
       MÉTODOS ADICIONALES
    ====================== */

    public function getUbigeoPadresAttribute()
    {
        return [
            'departamento' => optional($this->distrito->provincia->departamento)->ubigeo_dep,
            'provincia' => optional($this->distrito->provincia)->ubigeo_pro,
            'distrito' => $this->ubigeo_com
        ];
    }

    public function getDireccionCompletaAttribute()
    {
        return implode(' - ', array_filter([
            $this->direccion,
            $this->ubicacion_completa
        ]));
    }
    public function getDiscapacidadNombreAttribute()
    {
    return $this->discapacidad?->nombre_discapacidad ?? 'Ninguna';
    }

    
    
}
