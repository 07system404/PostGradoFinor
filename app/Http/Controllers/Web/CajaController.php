<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use App\Models\DetallePlanPago;
use App\Models\Pago;
use App\Models\Curso;
use App\Traits\CalculaEstadoFinanciero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CajaController extends Controller
{
    use CalculaEstadoFinanciero;

    /**
     * Pantalla principal de Caja y Facturación
     */
    public function index(Request $request)
    {
        // Totales globales SIEMPRE de TODOS los estudiantes (sin filtro)
        $todosEstudiantes = Estudiante::with(['inscripciones.planPago.detalles'])->latest()->get();
        $hoyGlobal = now()->startOfDay();

        $totalPagadoGlobal = 0;
        $totalDeudaGlobal = 0;

        foreach ($todosEstudiantes as $est) {
            foreach ($est->inscripciones as $insc) {
                if (! $insc->planPago) continue;
                $totalPagadoGlobal += $insc->planPago->monto_total_pagado;
                $totalDeudaGlobal  += $insc->planPago->saldo_pendiente;
            }
        }

        // Listar estudiantes (con o sin filtro de búsqueda)
        $query = Estudiante::query()
            ->with(['inscripciones.planPago.detalles', 'inscripciones.curso']);

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
        $hoy = now()->startOfDay();

        $estudiantesFinanciero = $estudiantes->map(function ($est) use ($hoy) {
            $estado = $this->calcularEstadoFinanciero($est, $hoy);

            $est->estado_financiero_label = $estado['label'];
            $est->total_programado        = $estado['total_programado'];
            $est->total_pagado            = $estado['total_pagado'];
            $est->saldo_pendiente         = $estado['saldo_pendiente'];
            $est->tiene_vencido           = $estado['tiene_vencido'];

            return $est;
        });

        // Estudiante seleccionado
        $estudianteSeleccionado = null;
        $inscripcionSeleccionada = null;
        $planPago = null;
        $detalles = collect();
        $montoProgramadoActivo = 0;
        $mostrarModalInscripcion = false;

        if ($request->filled('estudiante_id')) {
            $estudianteSeleccionado = Estudiante::with([
                'inscripciones.curso',
                'inscripciones.planPago.detalles.pagos',
            ])->find($request->input('estudiante_id'));

            // Si el estudiante no tiene inscripciones, mostrar modal en vez del detalle
            if ($estudianteSeleccionado && $estudianteSeleccionado->inscripciones->isEmpty()) {
                $mostrarModalInscripcion = true;
            }
        } elseif ($estudiantes->isNotEmpty()) {
            $estudianteSeleccionado = Estudiante::with([
                'inscripciones.curso',
                'inscripciones.planPago.detalles.pagos',
            ])->find($estudiantes->first()->id);

            if ($estudianteSeleccionado && $estudianteSeleccionado->inscripciones->isEmpty()) {
                $mostrarModalInscripcion = true;
            }
        }

        if ($estudianteSeleccionado && !$mostrarModalInscripcion) {
            if ($request->filled('inscripcion_id')) {
                $inscripcionSeleccionada = $estudianteSeleccionado->inscripciones
                    ->firstWhere('id', $request->input('inscripcion_id'));
            } else {
                $inscripcionSeleccionada = $estudianteSeleccionado->inscripciones->first();
            }

            if ($inscripcionSeleccionada) {
                $planPago = $inscripcionSeleccionada->planPago;
                if ($planPago) {
                    $detalles = $planPago->detalles()
                        ->where('estado', '!=', 'Condonado')
                        ->orderBy('nro_cuota')
                        ->get();

                    // Monto total activo (excluye cuotas condonadas)
                    $montoProgramadoActivo = $detalles->sum('monto_programado');
                }
            }
        }

        // URL del primer detalle pendiente/vencido para el botón "Registrar Pago"
        $urlPagoRapido = null;
        $primerDetallePendiente = DetallePlanPago::where('estado', '!=', 'Pagado')
            ->whereHas('planPago.inscripcion.estudiante', function ($q) {
                $q->where('activo', true);
            })
            ->with(['planPago.inscripcion.estudiante'])
            ->orderBy('fecha_vencimiento')
            ->first();
        if ($primerDetallePendiente) {
            $urlPagoRapido = route('caja.pago.formulario', $primerDetallePendiente->id);
        }

        return view('caja.index', compact(
            'estudiantesFinanciero',
            'estudianteSeleccionado',
            'inscripcionSeleccionada',
            'planPago',
            'detalles',
            'montoProgramadoActivo',
            'totalPagadoGlobal',
            'totalDeudaGlobal',
            'urlPagoRapido',
            'mostrarModalInscripcion'
        ));
    }

    /**
     * Detalle de cuenta de un estudiante específico
     */
    /**
     * Formulario de registro de pago.
     * - Con $detalleId: modo pre-llenado (desde "Cobrar" en cronograma)
     * - Sin $detalleId: modo vacío con búsqueda (desde "REGISTRAR Pago")
     */
    public function formularioPago($detalleId = null)
    {
        $referencia = 'FIN-' . date('Y') . '-' . str_pad(Pago::count() + 98442, 5, '0', STR_PAD_LEFT);

        if ($detalleId) {
            $detalle = DetallePlanPago::with(['planPago.inscripcion.estudiante', 'planPago.inscripcion.curso'])
                ->findOrFail($detalleId);
            $inscripcion = $detalle->planPago->inscripcion;
            $estudiante = $inscripcion->estudiante;

            return view('caja.pago', compact('detalle', 'inscripcion', 'estudiante', 'referencia'));
        }

        return view('caja.pago', [
            'detalle' => null,
            'inscripcion' => null,
            'estudiante' => null,
            'referencia' => $referencia,
        ]);
    }

    /**
     * Búsqueda AJAX de alumnos por nombre, cédula o registro
     */
    public function buscarAlumnos(Request $request)
    {
        $q = $request->get('q');
        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $estudiantes = Estudiante::where('nombres', 'like', "%{$q}%")
            ->orWhere('paterno', 'like', "%{$q}%")
            ->orWhere('materno', 'like', "%{$q}%")
            ->orWhere('cedula', 'like', "%{$q}%")
            ->orWhere('registro', 'like', "%{$q}%")
            ->with('inscripciones.curso')
            ->limit(10)
            ->get();

        return response()->json($estudiantes->map(fn($e) => [
            'id' => $e->id,
            'nombre_completo' => $e->nombre_completo,
            'cedula' => $e->cedula,
            'registro' => $e->registro,
            'inscripciones_count' => $e->inscripciones->count(),
            'inscripciones' => $e->inscripciones->map(fn($i) => [
                'id' => $i->id,
                'curso_nombre' => $i->curso->nombre,
                'tipo_inscripcion' => $i->tipo_inscripcion,
                'plan_pago_id' => $i->planPago?->id,
            ]),
        ]));
    }

    /**
     * Obtener detalles disponibles (no pagados) de un plan de pagos
     */
    public function detallesPorInscripcion($inscripcionId)
    {
        $inscripcion = Inscripcion::with('planPago.detalles')->findOrFail($inscripcionId);
        $planPago = $inscripcion->planPago;

        if (!$planPago) {
            return response()->json([]);
        }

        $detalles = $planPago->detalles
            ->where('estado', '!=', 'Condonado')
            ->values()
            ->map(fn($d) => [
                'id' => $d->id,
                'nro_cuota' => $d->nro_cuota,
                'concepto' => $d->concepto,
                'fase' => $d->fase,
                'monto_programado' => $d->monto_programado,
                'monto_pagado' => $d->monto_pagado,
                'saldo_cuota' => $d->saldo_cuota,
                'estado' => $d->estado,
                'esta_pagado' => in_array($d->estado, ['Pagado', 'Condonado']) || $d->saldo_cuota <= 0,
            ]);

        $matricula = $detalles->first(fn($d) =>
            ($d['fase'] ?? '') === 'Matrícula'
            || ($d['nro_cuota'] == 1 && stripos($d['concepto'] ?? '', 'Matr') !== false)
        );

        $modulos = $detalles->filter(fn($d) =>
            ($d['fase'] ?? '') !== 'Matrícula'
            && stripos($d['concepto'] ?? '', 'Matr') !== 0
            && stripos($d['concepto'] ?? '', 'Defensa') === false
        )->values();

        $defensas = $detalles->filter(fn($d) =>
            stripos($d['concepto'] ?? '', 'Defensa') !== false
        )->values();

        return response()->json([
            'matricula' => $matricula,
            'modulos' => $modulos,
            'defensas' => $defensas,
        ]);
    }

    /**
     * Recibo / detalle de los pagos realizados sobre una cuota.
     * Muestra cómo se hizo cada pago (monto, fecha, comprobante, observación)
     * y permite visualizar o descargar la imagen/PDF adjunto.
     */
    public function recibo($detalleId)
    {
        $detalle = DetallePlanPago::with([
            'pagos',
            'planPago.inscripcion.estudiante',
            'planPago.inscripcion.curso',
        ])->findOrFail($detalleId);

        $inscripcion = $detalle->planPago->inscripcion;
        $estudiante = $inscripcion->estudiante;
        $pagos = $detalle->pagos->sortByDesc('fecha_pago');

        return view('caja.recibo', compact('detalle', 'inscripcion', 'estudiante', 'pagos'));
    }
}
