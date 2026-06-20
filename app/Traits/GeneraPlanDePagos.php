<?php

namespace App\Traits;

use App\Models\Curso;
use App\Models\DetallePlanPago;
use App\Models\Inscripcion;
use App\Models\PlanPago;

/**
 * Genera el cronograma de pagos (Matrícula + Módulos + Defensas) de una
 * inscripción según el tipo de programa.
 *
 * Es la MISMA lógica que se usa al inscribir desde el Perfil del Alumno,
 * desde Caja y Finanzas, y desde el detalle del Programa, centralizada
 * aquí para que exista un único punto de verdad.
 */
trait GeneraPlanDePagos
{
    protected function crearPlanDePagos(
        Inscripcion $inscripcion,
        Curso $curso,
        float $descuento,
        string $modalidadPago,
        string $tipoInscripcion
    ): void {
        $nroModulosD = $curso->nro_modulos_diplomado;
        $nroModulosE = $curso->nro_modulos_especialidad ?? 0;
        $nroModulosM = $curso->nro_modulos_maestria ?? 0;

        $factorDescuento = 1 - $descuento;

        $detallePlan = [];
        $nroCuota = 0;

        // 1. Matrícula (siempre primero)
        $nroCuota++;
        $montoMatricula = round($curso->costo_matricula, 2);
        $detallePlan[] = [
            'nro_cuota' => $nroCuota, 'nro_modulo' => 0,
            'concepto' => 'Matrícula', 'fase' => 'Matrícula',
            'monto_programado' => $montoMatricula,
            'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $montoMatricula,
            'fecha_vencimiento' => now()->addDay(7), 'estado' => 'Pendiente',
        ];

        // 2. Módulos Diplomado
        $acumulado = 0;
        $costoModuloBase = $nroModulosD + $nroModulosE + $nroModulosM > 0
            ? $curso->costo_total_estudio / ($nroModulosD + $nroModulosE + $nroModulosM)
            : 0;

        for ($i = 1; $i <= $nroModulosD; $i++) {
            $nroCuota++;
            $monto = $i < $nroModulosD ? $costoModuloBase * $factorDescuento
                : $curso->costo_total_estudio * $factorDescuento - $acumulado
                  - ($nroModulosE + $nroModulosM) * $costoModuloBase * $factorDescuento;
            $monto = round($monto, 2);
            $acumulado += $costoModuloBase * $factorDescuento;
            $detallePlan[] = [
                'nro_cuota' => $nroCuota, 'nro_modulo' => $i,
                'concepto' => 'Módulo ' . $i, 'fase' => 'Diplomado',
                'monto_programado' => $monto,
                'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                'fecha_vencimiento' => now()->addMonths($nroCuota), 'estado' => 'Pendiente',
            ];
        }

        // 3. Defensa Diplomado
        if ($curso->costo_defensa_diplomado > 0 && in_array($tipoInscripcion, ['Diplomado', 'Especialidad', 'Maestría'])) {
            $nroCuota++;
            $monto = $curso->costo_defensa_diplomado;
            $detallePlan[] = [
                'nro_cuota' => $nroCuota, 'nro_modulo' => null,
                'concepto' => 'Defensa Diplomado', 'fase' => 'Defensa',
                'monto_programado' => $monto,
                'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                'fecha_vencimiento' => now()->addMonths($nroCuota), 'estado' => 'Pendiente',
            ];
        }

        // 4. Módulos Especialidad
        if (in_array($tipoInscripcion, ['Especialidad', 'Maestría'])) {
            for ($i = 1; $i <= $nroModulosE; $i++) {
                $nroCuota++;
                $monto = ($i === $nroModulosE && $nroModulosM === 0)
                    ? round($curso->costo_total_estudio * $factorDescuento - $acumulado, 2)
                    : round($costoModuloBase * $factorDescuento, 2);
                $acumulado += $costoModuloBase * $factorDescuento;
                $detallePlan[] = [
                    'nro_cuota' => $nroCuota, 'nro_modulo' => $nroModulosD + $i,
                    'concepto' => 'Módulo ' . ($nroModulosD + $i), 'fase' => 'Especialidad',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($nroCuota), 'estado' => 'Pendiente',
                ];
            }

            if ($curso->costo_defensa_especialidad > 0) {
                $nroCuota++;
                $monto = $curso->costo_defensa_especialidad;
                $detallePlan[] = [
                    'nro_cuota' => $nroCuota, 'nro_modulo' => null,
                    'concepto' => 'Defensa Especialidad', 'fase' => 'Defensa',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($nroCuota), 'estado' => 'Pendiente',
                ];
            }
        }

        // 5. Módulos Maestría
        if ($tipoInscripcion === 'Maestría') {
            for ($i = 1; $i <= $nroModulosM; $i++) {
                $nroCuota++;
                $monto = $i === $nroModulosM
                    ? round($curso->costo_total_estudio * $factorDescuento - $acumulado, 2)
                    : round($costoModuloBase * $factorDescuento, 2);
                $acumulado += $costoModuloBase * $factorDescuento;
                $detallePlan[] = [
                    'nro_cuota' => $nroCuota, 'nro_modulo' => $nroModulosD + $nroModulosE + $i,
                    'concepto' => 'Módulo ' . ($nroModulosD + $nroModulosE + $i), 'fase' => 'Maestría',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($nroCuota), 'estado' => 'Pendiente',
                ];
            }

            if ($curso->costo_defensa_maestria > 0) {
                $nroCuota++;
                $monto = $curso->costo_defensa_maestria;
                $detallePlan[] = [
                    'nro_cuota' => $nroCuota, 'nro_modulo' => null,
                    'concepto' => 'Defensa Maestría', 'fase' => 'Defensa',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($nroCuota), 'estado' => 'Pendiente',
                ];
            }
        }

        $montoTotalProgramado = collect($detallePlan)->sum('monto_programado');

        $plan = PlanPago::create([
            'inscripcion_id' => $inscripcion->id,
            'monto_total_programado' => $montoTotalProgramado,
            'monto_total_pagado' => 0,
            'saldo_pendiente' => $montoTotalProgramado,
            'total_cuotas' => count($detallePlan),
            'estado' => 'Pendiente',
        ]);

        foreach ($detallePlan as $detalle) {
            DetallePlanPago::create(array_merge($detalle, ['plan_pago_id' => $plan->id]));
        }
    }
}
