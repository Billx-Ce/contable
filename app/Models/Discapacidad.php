<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discapacidad extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_discapacidad'
    ];

    // Relación: Una discapacidad puede tener muchas personas
    public function personas()
    {
        return $this->hasMany(Persona::class);
    }
}
