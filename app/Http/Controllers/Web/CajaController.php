<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use App\Models\DetallePlanPago;
use App\Models\Pago;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CajaController extends Controller
{
    /**
     * Pantalla principal de Caja y Facturación
     */
    public function index(Request $request)
    {
        $query = Estudiante::with(['inscripciones.planPago.detalles', 'inscripciones.curso']);

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                  ->orWhere('paterno', 'like', "%{$buscar}%")
                  ->orWhere('materno', 'like', "%{$buscar}%")
                  ->orWhere('registro', 'like', "%{$buscar}%")
                  ->orWhere('cedula', 'like', "%{$buscar}%");
            });
        }

        $estudiantes = $query->latest()->get();

        // Determinar estado financiero de cada estudiante
        $estudiantesFinanciero = $estudiantes->map(function ($est) {
            $estadoFinanciero = 'Sin Pagar';
            $totalProgramado = 0;
            $totalPagado = 0;
            $saldoPendiente = 0;
            $tieneVencido = false;

            foreach ($est->inscripciones as $insc) {
                if ($insc->planPago) {
                    $totalProgramado += $insc->planPago->monto_total_programado;
                    $totalPagado += $insc->planPago->monto_total_pagado;
                    $saldoPendiente += $insc->planPago->saldo_pendiente;

                    // Verificar si tiene vencidos
                    foreach ($insc->planPago->detalles as $detalle) {
                        if ($detalle->estado === 'Vencido' ||
                            ($detalle->estado === 'Pendiente' && $detalle->fecha_vencimiento && $detalle->fecha_vencimiento < now())) {
                            $tieneVencido = true;
                        }
                    }
                }
            }

            if ($totalPagado >= $totalProgramado && $totalProgramado > 0) {
                $estadoFinanciero = 'Al Día';
            } elseif ($totalPagado > 0) {
                if ($tieneVencido) {
                    $estadoFinanciero = 'En Mora';
                } else {
                    $estadoFinanciero = 'Parcial';
                }
            } else {
                $estadoFinanciero = 'Sin Pagar';
            }

            $est->estado_financiero_label = $estadoFinanciero;
            $est->total_programado = $totalProgramado;
            $est->total_pagado = $totalPagado;
            $est->saldo_pendiente = $saldoPendiente;
            $est->tiene_vencido = $tieneVencido;

            return $est;
        });

        // Estudiante seleccionado (primero por defecto o por request)
        $estudianteSeleccionado = null;
        $inscripcionSeleccionada = null;
        $planPago = null;
        $detalles = collect();

        if ($request->filled('estudiante_id')) {
            $estudianteSeleccionado = Estudiante::with([
                'inscripciones.curso',
                'inscripciones.planPago.detalles.pagos',
            ])->find($request->input('estudiante_id'));
        } elseif ($estudiantes->isNotEmpty()) {
            $estudianteSeleccionado = Estudiante::with([
                'inscripciones.curso',
                'inscripciones.planPago.detalles.pagos',
            ])->find($estudiantes->first()->id);
        }

        if ($estudianteSeleccionado) {
            // Inscripción seleccionada (por defecto la primera, o por request)
            if ($request->filled('inscripcion_id')) {
                $inscripcionSeleccionada = $estudianteSeleccionado->inscripciones
                    ->firstWhere('id', $request->input('inscripcion_id'));
            } else {
                $inscripcionSeleccionada = $estudianteSeleccionado->inscripciones->first();
            }

            if ($inscripcionSeleccionada) {
                $planPago = $inscripcionSeleccionada->planPago;
                if ($planPago) {
                    $detalles = $planPago->detalles()->orderBy('nro_cuota')->get();
                }
            }
        }

        return view('caja.index', compact(
            'estudiantesFinanciero',
            'estudianteSeleccionado',
            'inscripcionSeleccionada',
            'planPago',
            'detalles'
        ));
    }

    /**
     * Detalle de cuenta de un estudiante específico
     */
    public function show($id)
    {
        $estudiante = Estudiante::with([
            'inscripciones.curso',
            'inscripciones.planPago.detalles.pagos',
        ])->findOrFail($id);

        $inscripciones = $estudiante->inscripciones;

        return view('caja.show', compact('estudiante', 'inscripciones'));
    }

    /**
     * Registrar un nuevo pago
     */
    public function registrarPago(Request $request)
    {
        $request->validate([
            'detalle_plan_pago_id' => 'required|exists:detalle_plan_pagos,id',
            'inscripcion_id' => 'required|exists:inscripciones,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'nro_comprobante' => 'required|string|max:50',
            'observacion' => 'nullable|string',
            'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $detalle = DetallePlanPago::findOrFail($request->detalle_plan_pago_id);
        $planPago = $detalle->planPago;

        // Subir archivo si existe
        $archivoAdjunto = null;
        if ($request->hasFile('comprobante')) {
            $archivoAdjunto = $request->file('comprobante')->store('comprobantes', 'public');
        }

        // Crear el pago
        $pago = Pago::create([
            'detalle_plan_pago_id' => $request->detalle_plan_pago_id,
            'inscripcion_id' => $request->inscripcion_id,
            'fecha_pago' => $request->fecha_pago,
            'monto' => $request->monto,
            'nro_comprobante' => $request->nro_comprobante,
            'observacion' => $request->observacion,
            'archivo_adjunto' => $archivoAdjunto,
        ]);

        // Actualizar detalle del plan
        $nuevoMontoPagado = $detalle->monto_pagado + $request->monto;
        $nuevoSaldo = $detalle->monto_programado - $nuevoMontoPagado;

        if ($nuevoSaldo <= 0) {
            $detalle->estado = 'Pagado';
        } else {
            $detalle->estado = 'Parcial';
        }

        $detalle->monto_pagado = $nuevoMontoPagado;
        $detalle->saldo_cuota = max(0, $nuevoSaldo);
        $detalle->save();

        // Actualizar plan de pagos
        $totalPagado = $planPago->detalles->sum('monto_pagado');
        $saldoPendiente = $planPago->monto_total_programado - $totalPagado;

        $planPago->monto_total_pagado = $totalPagado;
        $planPago->saldo_pendiente = max(0, $saldoPendiente);

        if ($saldoPendiente <= 0) {
            $planPago->estado = 'Pagado';
        } else {
            $planPago->estado = 'En Mora';
        }
        $planPago->save();

        // Actualizar estado financiero de la inscripción
        $inscripcion = Inscripcion::find($request->inscripcion_id);
        if ($saldoPendiente <= 0) {
            $inscripcion->estado_financiero = 'Completado';
        } elseif ($totalPagado > 0) {
            $inscripcion->estado_financiero = 'Parcial';
        }
        $inscripcion->save();

        return redirect()->route('caja.index', [
            'estudiante_id' => $inscripcion->estudiante_id,
            'inscripcion_id' => $inscripcion->id,
        ])->with('success', 'Pago registrado correctamente. Referencia: ' . $request->nro_comprobante);
    }

    /**
     * Formulario de registro de pago (pantalla individual)
     */
    public function formularioPago($detalleId)
    {
        $detalle = DetallePlanPago::with(['planPago.inscripcion.estudiante', 'planPago.inscripcion.curso'])
            ->findOrFail($detalleId);

        $inscripcion = $detalle->planPago->inscripcion;
        $estudiante = $inscripcion->estudiante;

        // Generar referencia de pago
        $referencia = 'FIN-' . date('Y') . '-' . str_pad(Pago::count() + 98442, 5, '0', STR_PAD_LEFT);

        return view('caja.pago', compact('detalle', 'inscripcion', 'estudiante', 'referencia'));
    }
}
