<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'version',
        'edicion',
        'periodo',
        'costo_matricula',
        'costo_total_estudio',
        'costo_defensa_diplomado',
        'costo_defensa_especialidad',
        'costo_defensa_maestria',
        'nro_modulos_diplomado',
        'nro_modulos_especialidad',
        'nro_modulos_maestria',
        'cupo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    //  Relaciones

    public function getNroModulosForTipo(string $tipo): int
    {
        return match ($tipo) {
            'Diplomado' => $this->nro_modulos_diplomado,
            'Especialidad' => $this->nro_modulos_especialidad ?? 0,
            'Maestría' => $this->nro_modulos_maestria ?? 0,
            default => 0,
        };
    }

    public function getCostoDefensaForTipo(string $tipo): float
    {
        return (float) match ($tipo) {
            'Diplomado' => $this->costo_defensa_diplomado,
            'Especialidad' => $this->costo_defensa_especialidad,
            'Maestría' => $this->costo_defensa_maestria,
            default => 0.0,
        };
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
