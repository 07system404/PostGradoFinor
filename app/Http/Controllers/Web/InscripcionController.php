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

            $curso = Curso::find($validated['curso_id']);
            $descuento = $estudiante->descuento_porcentaje / 100;

            $this->crearPlanDePagos(
                $inscripcion,
                $curso,
                $descuento,
                $validated['modalidad_pago'],
                $validated['tipo_inscripcion']
            );
        });

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('success', 'Inscripción agregada correctamente.');
    }

    private function crearPlanDePagos(
        Inscripcion $inscripcion,
        Curso $curso,
        float $descuento,
        string $modalidadPago,
        string $tipoInscripcion
    ): void {
        $nroModulos = $curso->getNroModulosForTipo($tipoInscripcion);
        $costoDefensa = $curso->getCostoDefensaForTipo($tipoInscripcion);
        $totalEstudio = $curso->costo_total_estudio;
        $totalConDescuento = $totalEstudio - ($totalEstudio * $descuento);
        $totalProgramado = $totalConDescuento + $costoDefensa;

        $detallePlan = [];
        if ($nroModulos > 0) {
            $montoModulo = round($totalConDescuento / $nroModulos, 2);
            $montoAcumulado = 0;
            for ($i = 1; $i <= $nroModulos; $i++) {
                $monto = $i === $nroModulos ? $totalConDescuento - $montoAcumulado : $montoModulo;
                $montoAcumulado += $monto;
                $detallePlan[] = [
                    'nro_cuota' => $i,
                    'nro_modulo' => $i,
                    'concepto' => 'Módulo '.$i,
                    'fase' => 'Módulo',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0,
                    'monto_descuento' => 0,
                    'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($i),
                    'estado' => 'Pendiente',
                ];
            }
        }

        if ($costoDefensa > 0) {
            $detallePlan[] = [
                'nro_cuota' => count($detallePlan) + 1,
                'nro_modulo' => null,
                'concepto' => 'Defensa',
                'fase' => 'Defensa',
                'monto_programado' => $costoDefensa,
                'monto_pagado' => 0,
                'monto_descuento' => 0,
                'saldo_cuota' => $costoDefensa,
                'fecha_vencimiento' => now()->addMonths(count($detallePlan) + 1),
                'estado' => 'Pendiente',
            ];
        }

        if (empty($detallePlan)) {
            $detallePlan[] = [
                'nro_cuota' => 1,
                'nro_modulo' => null,
                'concepto' => 'Programa completo',
                'fase' => $tipoInscripcion,
                'monto_programado' => $totalProgramado,
                'monto_pagado' => 0,
                'monto_descuento' => 0,
                'saldo_cuota' => $totalProgramado,
                'fecha_vencimiento' => now()->addMonth(),
                'estado' => 'Pendiente',
            ];
        }

        $plan = \App\Models\PlanPago::create([
            'inscripcion_id' => $inscripcion->id,
            'monto_total_programado' => $totalProgramado,
            'monto_total_pagado' => 0,
            'saldo_pendiente' => $totalProgramado,
            'total_cuotas' => count($detallePlan),
            'estado' => 'Pendiente',
        ]);

        foreach ($detallePlan as $detalle) {
            \App\Models\DetallePlanPago::create(array_merge($detalle, ['plan_pago_id' => $plan->id]));
        }
    }
}