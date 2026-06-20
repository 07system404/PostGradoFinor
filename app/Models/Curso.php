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

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
