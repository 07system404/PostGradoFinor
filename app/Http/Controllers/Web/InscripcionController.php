<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    /**
     * Mostrar formulario para inscribir a un estudiante en un nuevo curso (modal).
     */
    public function create(Estudiante $estudiante)
    {
        $cursos = Curso::where('activo', true)->get();
        return view('estudiantes.modal_inscripcion', compact('estudiante', 'cursos'));
    }

    /**
     * Guardar nueva inscripción para un estudiante existente.
     */
    public function store(Request $request, Estudiante $estudiante)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'fecha_inscripcion' => 'required|date',
            'modalidad_pago' => 'required|in:Contado,Cuotas',
            'tipo_inscripcion' => 'required|in:Diplomado,Especialidad,Maestría',
        ]);

        // Verificar que no esté ya inscrito en ese curso
        $existe = Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('curso_id', $validated['curso_id'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['curso_id' => 'El estudiante ya está inscrito en este curso.']);
        }

        DB::transaction(function () use ($estudiante, $validated) {
            $inscripcion = Inscripcion::create([
                'estudiante_id' => $estudiante->id,
                'curso_id' => $validated['curso_id'],
                'tipo_inscripcion' => $validated['tipo_inscripcion'],
                'fecha_inscripcion' => $validated['fecha_inscripcion'],
                'estado_academico' => 'Pendiente',
                'estado_financiero' => 'Sin Pagar',
                'modalidad_pago' => $validated['modalidad_pago'],
                'observacion' => null,
            ]);

            // Crear plan de pagos igual que en el store de estudiante
            $curso = Curso::find($validated['curso_id']);
            $total = $curso->costo_total_estudio;
            $descuento = $estudiante->descuento_porcentaje / 100;
            $totalConDescuento = $total - ($total * $descuento);

            $plan = \App\Models\PlanPago::create([
                'inscripcion_id' => $inscripcion->id,
                'monto_total_programado' => $totalConDescuento,
                'monto_total_pagado' => 0,
                'saldo_pendiente' => $totalConDescuento,
                'total_cuotas' => $validated['modalidad_pago'] === 'Contado' ? 1 : 6,
                'estado' => 'Pendiente',
            ]);

            $cuotas = $validated['modalidad_pago'] === 'Contado' ? 1 : 6;
            $montoCuota = $totalConDescuento / $cuotas;
            for ($i = 1; $i <= $cuotas; $i++) {
                \App\Models\DetallePlanPago::create([
                    'plan_pago_id' => $plan->id,
                    'nro_cuota' => $i,
                    'nro_modulo' => null,
                    'concepto' => 'Cuota '.$i,
                    'fase' => null,
                    'monto_programado' => $montoCuota,
                    'monto_pagado' => 0,
                    'monto_descuento' => 0,
                    'saldo_cuota' => $montoCuota,
                    'fecha_vencimiento' => now()->addMonths($i),
                    'estado' => 'Pendiente',
                ]);
            }
        });

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('success', 'Inscripción agregada correctamente.');
    }
}