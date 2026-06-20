<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'detalle_plan_pago_id',
        'inscripcion_id',
        'fecha_pago',
        'monto',
        'nro_comprobante',
        'observacion',
        'archivo_adjunto',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
    ];

    //  Relaciones

    public function detallePlanPago()
    {
        return $this->belongsTo(DetallePlanPago::class, 'detalle_plan_pago_id');
    }

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }
}