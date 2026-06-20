<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPago extends Model
{
    protected $table = 'plan_pagos';

    protected $fillable = [
        'inscripcion_id',
        'monto_total_programado',
        'monto_total_pagado',
        'saldo_pendiente',
        'total_cuotas',
        'estado',
    ];

    //  Relaciones

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetallePlanPago::class, 'plan_pago_id');
    }

    //  Métodos

    public function recalcularTotales(): void
    {
        $this->load('detalles');

        $totalPagado    = $this->detalles->sum('monto_pagado');
        $saldoPendiente = $this->detalles->sum('saldo_cuota');

        $this->update([
            'monto_total_pagado' => $totalPagado,
            'saldo_pendiente'    => $saldoPendiente,
            'estado'             => $saldoPendiente <= 0 ? 'Pagado' : 'Pendiente',
        ]);
    }
}