<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    // Orden de tipos de inscripción para comparación (mayor = más fases)
    const TIPO_ORDER = [
        'Diplomado' => 1,
        'Especialidad' => 2,
        'Maestría' => 3,
    ];

    protected $fillable = [
        'estudiante_id',
        'curso_id',
        'tipo_inscripcion',
        'fecha_inscripcion',
        'estado_academico',
        'estado_financiero',
        'modalidad_pago',
        'observacion',
    ];

    protected $casts = [
        'fecha_inscripcion' => 'date',
    ];

    //  Relaciones

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function planPago()
    {
        return $this->hasOne(PlanPago::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}