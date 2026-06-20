<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePlanPago extends Model
{
    protected $table = 'detalle_plan_pagos';

    protected $fillable = [
        'plan_pago_id',
        'nro_cuota',
        'nro_modulo',
        'concepto',
        'fase',
        'monto_programado',
        'monto_pagado',
        'monto_descuento',
        'saldo_cuota',
        'fecha_vencimiento',
        'estado',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    //  Relaciones

    public function planPago()
    {
        return $this->belongsTo(PlanPago::class, 'plan_pago_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'detalle_plan_pago_id');
    }
}