<?php

namespace App\Traits;

use App\Models\Inscripcion;
use App\Models\DetallePlanPago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Lógica compartida para:
 *   - Dar de baja una inscripción (Retirado, condonar cuotas futuras)
 *   - Reactivar una inscripción (volver cuotas condonadas a Pendiente con fechas recalculadas)
 *   - Cambiar tipo de inscripción (Solo Diplomado / Solo Especialidad)
 *   - Continuar a la siguiente fase
 *
 * Se usa tanto en EstudianteController como en CursoController.
 */
trait ManejaBajaReactivacion
{
    /**
     * Intervalo estándar: primera cuota vence a los 7 días de la fecha base.
     */
    protected function primerVencimientoOffset(): string
    {
        return '+7 days';
    }

    /**
     * Intervalo estándar entre cuotas consecutivas: 1 mes.
     */
    protected function intervaloEntreCuotas(): string
    {
        return '+1 month';
    }

    // ──────────────────────────────────────────────
    //  BAJA (Contexto 1 y Contexto 2)
    // ──────────────────────────────────────────────

    /**
     * Aplica la baja a UNA inscripción específica:
     * - estado_academico = 'Retirado'
     * - observacion = 'DIO BAJA' (reemplaza cualquier valor anterior)
     * - Condona cuotas Pendientes con fecha_vencimiento > hoy
     * - Cuotas vencidas sin pagar se mantienen como deuda real
     * - Cuotas pagadas se mantienen intactas
     */
    protected function bajarInscripcion(Inscripcion $inscripcion): void
    {
        DB::transaction(function () use ($inscripcion) {
            $hoy = now()->startOfDay();

            // 1. Actualizar estado_academico y observacion (siempre sobrescribe)
            $inscripcion->update([
                'estado_academico' => 'Retirado',
                'observacion'      => 'DIO BAJA',
            ]);

            // 2. Cargar detalles del plan de pagos
            if (! $inscripcion->relationLoaded('planPago.detalles')) {
                $inscripcion->load('planPago.detalles');
            }

            if (! $inscripcion->planPago) return;

            foreach ($inscripcion->planPago->detalles as $detalle) {
                // Cuotas ya pagadas → no tocar
                if ($detalle->estado === 'Pagado') continue;

                // Cuotas ya condonadas → no tocar
                if ($detalle->estado === 'Condonado') continue;

                // Cuotas Pendientes con fecha_vencimiento > hoy → condonar (futuras)
                if ($detalle->fecha_vencimiento && $detalle->fecha_vencimiento->gt($hoy)) {
                    $detalle->update([
                        'estado'      => 'Condonado',
                        'saldo_cuota' => 0,
                    ]);
                }
                // Cuotas Pendientes con fecha_vencimiento <= hoy (vencidas) → no tocar (deuda real)
                // Cuotas en estado 'Vencido' → no tocar
            }

            // 3. Recalcular totales del plan
            $inscripcion->planPago->recalcularTotales();
        });
    }

    // ──────────────────────────────────────────────
    //  REACTIVACIÓN DE UNA INSCRIPCIÓN
    // ──────────────────────────────────────────────

    /**
     * Reactiva una inscripción que estaba en estado 'Retirado':
     * - estado_academico = 'Activo'
     * - observacion = null (se limpia, reemplaza cualquier valor anterior)
     * - Las cuotas Condonadas (que se condonaron por baja) vuelven a 'Pendiente'
     *   con fecha_vencimiento RECALCULADA hacia adelante (Corrección 2)
     * - Cuotas pagadas se mantienen intactas
     * - Cuotas vencidas sin pagar se mantienen como deuda pendiente
     */
    protected function procesarReactivacionInscripcion(Inscripcion $inscripcion): void
    {
        DB::transaction(function () use ($inscripcion) {
            $hoy = now()->startOfDay();

            // 1. Actualizar estado_academico y observacion (siempre sobrescribe → null)
            $inscripcion->update([
                'estado_academico' => 'Activo',
                'observacion'      => null,
            ]);

            if (! $inscripcion->planPago) return;

            if (! $inscripcion->relationLoaded('planPago.detalles')) {
                $inscripcion->load('planPago.detalles');
            }

            // 2. Recalcular fechas de las cuotas condonadas
            $detallesCondonados = $inscripcion->planPago->detalles
                ->where('estado', 'Condonado')
                ->sortBy('nro_cuota');

            $indice = 0;
            foreach ($detallesCondonados as $detalle) {
                // Recalcular fecha_vencimiento desde HOY
                $nuevaFecha = $this->calcularFechaReactivacion($hoy, $indice);

                $detalle->update([
                    'estado'           => 'Pendiente',
                    'saldo_cuota'      => $detalle->monto_programado - $detalle->monto_pagado,
                    'fecha_vencimiento' => $nuevaFecha,
                ]);
                $indice++;
            }

            // 3. Recalcular totales del plan
            $inscripcion->planPago->recalcularTotales();
        });
    }

    /**
     * Calcula la fecha de vencimiento para una cuota reactivada.
     *
     * @param Carbon $fechaBase  Fecha de reactivación (hoy)
     * @param int    $indice     Índice de la cuota dentro de las reactivadas (0, 1, 2, ...)
     * @return Carbon
     */
    protected function calcularFechaReactivacion(Carbon $fechaBase, int $indice): Carbon
    {
        $fecha = clone $fechaBase;

        // Primera cuota reactivada: fechaBase + 7 días
        $fecha->addDays(7);

        // Cuotas subsiguientes: +1 mes cada una
        if ($indice > 0) {
            $fecha->addMonths($indice);
        }

        return $fecha;
    }

    // ──────────────────────────────────────────────
    //  CAMBIAR TIPO DE INSCRIPCIÓN (Solo Diplomado / Solo Especialidad)
    // ──────────────────────────────────────────────

    /**
     * Limita el tipo de inscripción a una fase inferior.
     * Condona todas las cuotas de fases posteriores a la elegida.
     *
     * @param Inscripcion $inscripcion
     * @param string      $nuevoTipo   'Diplomado' | 'Especialidad'
     */
    protected function limitarTipoInscripcion(Inscripcion $inscripcion, string $nuevoTipo): void
    {
        DB::transaction(function () use ($inscripcion, $nuevoTipo) {
            // observacion siempre sobrescribe con la etiqueta actual
            $inscripcion->update([
                'tipo_inscripcion' => $nuevoTipo,
                'observacion'      => 'SOLO ' . strtoupper($nuevoTipo),
            ]);

            if (! $inscripcion->planPago) return;

            if (! $inscripcion->relationLoaded('planPago.detalles')) {
                $inscripcion->load('planPago.detalles');
            }

            foreach ($inscripcion->planPago->detalles as $detalle) {
                // Ya pagado → dejar intacto
                if ($detalle->estado === 'Pagado') continue;

                // Determinar si este detalle debe condonarse según el nuevo tipo
                if ($this->detalleDebeCondonarse($detalle, $nuevoTipo)) {
                    $detalle->update([
                        'estado'      => 'Condonado',
                        'saldo_cuota' => 0,
                    ]);
                }
            }

            $inscripcion->planPago->recalcularTotales();
        });
    }

    /**
     * Determina si un detalle del plan de pagos debe condonarse al limitar
     * la inscripción a un tipo inferior.
     *
     * Las defensas comparten fase='Defensa', por eso se distingue por concepto.
     */
    private function detalleDebeCondonarse($detalle, string $nuevoTipo): bool
    {
        $conceptosDefensaPosteriores = match ($nuevoTipo) {
            'Diplomado' => ['Defensa Especialidad', 'Defensa Maestría'],
            'Especialidad' => ['Defensa Maestría'],
            default => [],
        };

        $fasesPosteriores = match ($nuevoTipo) {
            'Diplomado' => ['Especialidad', 'Maestría'],
            'Especialidad' => ['Maestría'],
            default => [],
        };

        // Condonar si la fase es posterior (módulos de niveles superiores)
        if (in_array($detalle->fase, $fasesPosteriores)) {
            return true;
        }

        // Condonar si es una defensa de un nivel posterior (identificado por concepto)
        if ($detalle->fase === 'Defensa' && in_array($detalle->concepto, $conceptosDefensaPosteriores)) {
            return true;
        }

        return false;
    }

    /**
     * Continúa a la siguiente fase (ej. Solo Diplomado → Especialidad, o Solo Especialidad → Maestría).
     * Las cuotas condonadas de la fase que se habilita vuelven a Pendiente con fechas recalculadas.
     * observacion se actualiza según el nuevo nivel alcanzado:
     *   - Si Especialidad → "SOLO ESPECIALIDAD" (aún hay restricción)
     *   - Si Maestría → null (sin restricciones, nivel máximo)
     */
    protected function aplicarContinuacionFase(Inscripcion $inscripcion): void
    {
        DB::transaction(function () use ($inscripcion) {
            $tipoActual = $inscripcion->tipo_inscripcion;
            $siguienteTipo = $this->siguienteTipo($tipoActual);

            if (! $siguienteTipo) {
                throw new \RuntimeException("No hay una fase superior a '{$tipoActual}'.");
            }

            $hoy = now()->startOfDay();

            // observacion: si sigue habiendo restricción (Especialidad) se anota;
            // si ya es Maestría (nivel máximo) se limpia
            $nuevaObs = match ($siguienteTipo) {
                'Especialidad' => 'SOLO ESPECIALIDAD',
                'Maestría' => null,
            };

            $inscripcion->update([
                'tipo_inscripcion' => $siguienteTipo,
                'observacion'      => $nuevaObs,
            ]);

            if (! $inscripcion->planPago) return;

            if (! $inscripcion->relationLoaded('planPago.detalles')) {
                $inscripcion->load('planPago.detalles');
            }

            // Conceptos de defensa que deben reactivarse al continuar
            $conceptosDefensaNuevos = match ($siguienteTipo) {
                'Especialidad' => ['Defensa Especialidad'],
                'Maestría' => ['Defensa Maestría'],
                default => [],
            };

            // Fases que se habilitan (módulos de la nueva fase)
            $fasesNuevas = match ($siguienteTipo) {
                'Especialidad' => ['Especialidad'],
                'Maestría' => ['Maestría'],
                default => [],
            };

            $detallesAReactivar = collect();

            foreach ($inscripcion->planPago->detalles as $detalle) {
                if ($detalle->estado !== 'Condonado') continue;

                // Reactivar módulos de la nueva fase
                if (in_array($detalle->fase, $fasesNuevas)) {
                    $detallesAReactivar->push($detalle);
                }

                // Reactivar defensa de la nueva fase (identificada por concepto)
                if ($detalle->fase === 'Defensa' && in_array($detalle->concepto, $conceptosDefensaNuevos)) {
                    $detallesAReactivar->push($detalle);
                }
            }

            $detallesAReactivar = $detallesAReactivar->sortBy('nro_cuota');

            $indice = 0;
            foreach ($detallesAReactivar as $detalle) {
                $nuevaFecha = $this->calcularFechaReactivacion($hoy, $indice);

                $detalle->update([
                    'estado'           => 'Pendiente',
                    'saldo_cuota'      => $detalle->monto_programado - $detalle->monto_pagado,
                    'fecha_vencimiento' => $nuevaFecha,
                ]);
                $indice++;
            }

            $inscripcion->planPago->recalcularTotales();
        });
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    /**
     * Devuelve las fases de detalle_plan_pagos posteriores a un tipo dado.
     */
    private function fasesPosterioresA(string $tipo): array
    {
        return match ($tipo) {
            'Diplomado' => ['Especialidad', 'Maestría'],
            'Especialidad' => ['Maestría'],
            default => [],
        };
    }

    /**
     * Devuelve el siguiente tipo de inscripción superior.
     */
    private function siguienteTipo(string $tipoActual): ?string
    {
        return match ($tipoActual) {
            'Diplomado' => 'Especialidad',
            'Especialidad' => 'Maestría',
            default => null,
        };
    }

    /**
     * Devuelve todas las fases que componen un tipo de inscripción.
     */
    private function fasesDelTipo(string $tipo): array
    {
        return match ($tipo) {
            'Diplomado' => ['Matrícula', 'Diplomado', 'Defensa'],
            'Especialidad' => ['Matrícula', 'Diplomado', 'Defensa', 'Especialidad'],
            'Maestría' => ['Matrícula', 'Diplomado', 'Defensa', 'Especialidad', 'Maestría'],
            default => [],
        };
    }

    /**
     * Determina si una inscripción puede limitarse a un tipo inferior.
     */
    protected function puedeLimitarA(Inscripcion $inscripcion): array
    {
        $opciones = [];
        $tipo = $inscripcion->tipo_inscripcion;

        if (in_array($tipo, ['Especialidad', 'Maestría'])) {
            $opciones[] = ['value' => 'Diplomado', 'label' => 'Solo Diplomado'];
        }
        if ($tipo === 'Maestría') {
            $opciones[] = ['value' => 'Especialidad', 'label' => 'Solo Especialidad'];
        }

        return $opciones;
    }

    /**
     * Determina si una inscripción puede continuar a la siguiente fase.
     */
    protected function puedeContinuar(Inscripcion $inscripcion): bool
    {
        return $this->siguienteTipo($inscripcion->tipo_inscripcion) !== null
            && $inscripcion->estado_academico !== 'Retirado';
    }
}
