<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DetallePlanPago;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function create(Estudiante $estudiante, DetallePlanPago $detalle)
    {
        // Verificar que el detalle pertenezca a un plan del estudiante
        $inscripcion = $detalle->planPago->inscripcion;
        if ($inscripcion->estudiante_id != $estudiante->id) {
            abort(404);
        }

        $referencia = 'FIN-' . date('Y') . '-' . str_pad(Pago::count() + 98442, 5, '0', STR_PAD_LEFT);

        return view('caja.pago', compact('estudiante', 'detalle', 'inscripcion', 'referencia'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'detalle_plan_pago_id' => 'required|exists:detalle_plan_pagos,id',
            'fecha_pago' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'nro_comprobante' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $estudianteId = null;

        DB::transaction(function () use ($request, &$estudianteId) {
            $detalle = DetallePlanPago::findOrFail($request->detalle_plan_pago_id);
            $plan = $detalle->planPago;
            $inscripcion = $plan->inscripcion;
            $estudianteId = $inscripcion->estudiante_id;

            // Verificar que el monto no supere el saldo de la cuota
            $saldoCuota = $detalle->saldo_cuota;
            if ($request->monto > $saldoCuota) {
                throw new \Exception('El monto excede el saldo pendiente de la cuota.');
            }

            // Crear el pago
            $pago = Pago::create([
                'detalle_plan_pago_id' => $detalle->id,
                'inscripcion_id' => $inscripcion->id,
                'fecha_pago' => $request->fecha_pago,
                'monto' => $request->monto,
                'nro_comprobante' => $request->nro_comprobante,
                'observacion' => $request->observacion,
            ]);

            // Si se subió archivo, guardar
            if ($request->hasFile('comprobante')) {
                $path = $request->file('comprobante')->store('pagos', 'public');
                $pago->archivo_adjunto = $path;
                $pago->save();
            }

            // Actualizar detalle plan pago
            $detalle->monto_pagado += $request->monto;
            $detalle->saldo_cuota = $detalle->monto_programado - $detalle->monto_pagado;
            if ($detalle->saldo_cuota <= 0) {
                $detalle->estado = 'Pagado';
            } else {
                $detalle->estado = 'Parcial';
            }
            $detalle->save();

            // Actualizar plan pago
            $plan->monto_total_pagado += $request->monto;
            $plan->saldo_pendiente = $plan->monto_total_programado - $plan->monto_total_pagado;
            if ($plan->saldo_pendiente <= 0) {
                $plan->estado = 'Pagado';
            } else {
                // Verificar si alguna cuota está vencida
                $vencidas = $plan->detalles->filter(function ($det) {
                    return $det->fecha_vencimiento && $det->fecha_vencimiento < now() && $det->estado != 'Pagado';
                });
                if ($vencidas->count() > 0) {
                    $plan->estado = 'En Mora';
                } else {
                    $plan->estado = 'Pendiente';
                }
            }
            $plan->save();

            // Actualizar estado financiero de la inscripción
            $inscripcion = Inscripcion::find($plan->inscripcion_id);
            if ($inscripcion) {
                if ($plan->saldo_pendiente <= 0) {
                    $inscripcion->estado_financiero = 'Completado';
                } elseif ($plan->monto_total_pagado > 0) {
                    $inscripcion->estado_financiero = 'Parcial';
                }
                $inscripcion->save();
            }
        });

        return redirect()->route('caja.index', ['estudiante_id' => $estudianteId])
            ->with('success', 'Pago registrado correctamente.');
    }
}