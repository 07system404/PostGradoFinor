<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Lógica compartida para determinar el estado financiero de un estudiante.
 *
 * Devuelve uno de estos estados:
 *   - "Sin Inscribir"        → no tiene ninguna inscripción
 *   - "Matrícula Pendiente"  → tiene inscripción pero aún no paga la matrícula
 *   - "En Mora"              → matrícula pagada pero con cuotas vencidas sin pagar
 *   - "Al Día"               → matrícula pagada y sin cuotas vencidas
 *
 * Se usa tanto en CajaController como en DashboardController para mantener
 * un único punto de verdad sobre el cálculo del estado.
 */
trait CalculaEstadoFinanciero
{
    /**
     * Calcula el estado financiero y los totales de un estudiante.
     *
     * El estudiante debe venir con las relaciones cargadas:
     *   inscripciones.planPago.detalles
     *
     * @return array{label:string,total_programado:float,total_pagado:float,saldo_pendiente:float,tiene_vencido:bool}
     */
    protected function calcularEstadoFinanciero($estudiante, ?Carbon $hoy = null): array
    {
        $hoy = $hoy ?? now()->startOfDay();

        if ($estudiante->inscripciones->isEmpty()) {
            return [
                'label'            => 'Sin Inscribir',
                'total_programado' => 0,
                'total_pagado'     => 0,
                'saldo_pendiente'  => 0,
                'tiene_vencido'    => false,
            ];
        }

        $totalProgramado = 0;
        $totalPagado     = 0;
        $saldoPendiente  = 0;
        $matriculaPagada = false;
        $tieneVencido    = false;

        foreach ($estudiante->inscripciones as $insc) {
            if (! $insc->planPago) continue;

            $totalProgramado += $insc->planPago->monto_total_programado;
            $totalPagado     += $insc->planPago->monto_total_pagado;
            $saldoPendiente  += $insc->planPago->saldo_pendiente;

            foreach ($insc->planPago->detalles as $detalle) {
                $saldoCuota = $detalle->monto_programado - $detalle->monto_pagado;
                $estaPagada = in_array($detalle->estado, ['Pagado', 'Condonado']) || $saldoCuota <= 0;

                $esMatricula = ($detalle->fase ?? '') === 'Matrícula'
                    || ($detalle->nro_cuota == 1 && stripos($detalle->concepto ?? '', 'Matr') !== false)
                    || ($detalle->nro_cuota == 1 && stripos($detalle->concepto ?? '', 'atric') !== false);

                if ($esMatricula && $estaPagada) {
                    $matriculaPagada = true;
                }

                $vencidaSinPagar = ! $estaPagada
                    && $detalle->fecha_vencimiento
                    && $detalle->fecha_vencimiento->lt($hoy);

                if ($detalle->estado === 'Vencido' || $vencidaSinPagar) {
                    $tieneVencido = true;
                }
            }
        }

        // Si el plan no tiene una fila explícita de matrícula pero ya está
        // todo pagado, asumimos que la matrícula quedó cubierta.
        $todasLasCuotas = $estudiante->inscripciones->flatMap(fn($i) => $i->planPago?->detalles ?? []);
        $tieneFilaMatricula = $todasLasCuotas->contains(fn($d) =>
            ($d->fase ?? '') === 'Matrícula'
            || ($d->nro_cuota == 1 && stripos($d->concepto ?? '', 'Matr') !== false)
        );
        if (! $tieneFilaMatricula && $todasLasCuotas->isNotEmpty()) {
            if ($saldoPendiente <= 0 && $totalPagado > 0) {
                $matriculaPagada = true;
            }
        }

        if (! $matriculaPagada) {
            $label = 'Matrícula Pendiente';
        } elseif ($tieneVencido) {
            $label = 'En Mora';
        } else {
            $label = 'Al Día';
        }

        return [
            'label'            => $label,
            'total_programado' => $totalProgramado,
            'total_pagado'     => $totalPagado,
            'saldo_pendiente'  => $saldoPendiente,
            'tiene_vencido'    => $tieneVencido,
        ];
    }
}
