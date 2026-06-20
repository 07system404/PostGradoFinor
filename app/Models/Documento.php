<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $fillable = [
        'estudiante_id',
        'tipo',
        'nombre_archivo',
        'ruta_archivo',
    ];

    //  Relaciones

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}