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
        'total_modulos',
        'costo_modulo',
        'cupo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'costo_modulo' => 'decimal:2',
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

    /**
     * Total de módulos según el tipo de programa: las etapas son acumulativas
     * (para llegar a Especialidad se cursa Diplomado; para Maestría, las tres).
     */
    public static function calcularTotalModulos(string $tipo, int $diplomado, int $especialidad, int $maestria): int
    {
        return match ($tipo) {
            'Diplomado' => $diplomado,
            'Especialidad' => $diplomado + $especialidad,
            'Maestría' => $diplomado + $especialidad + $maestria,
            default => $diplomado,
        };
    }

    /**
     * Costo de cada módulo/cuota = Costo Total de Estudios ÷ Total de módulos.
     */
    public static function calcularCostoModulo(float $costoTotalEstudio, int $totalModulos): float
    {
        return $totalModulos > 0 ? round($costoTotalEstudio / $totalModulos, 2) : 0.0;
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
