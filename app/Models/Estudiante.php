<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estudiante extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombres',
        'paterno',
        'materno',
        'registro',
        'cedula',
        'celular',
        'observaciones',
        'descuento_porcentaje',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    //  Accessors

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->paterno} {$this->materno}");
    }

    //  Relaciones

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}