<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\PlanPago;
use App\Models\DetallePlanPago;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PostGradoSeeder extends Seeder
{
    public function run(): void
    {
        // ─── USUARIOS ─────────────────────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@finor.edu.bo'],
            [
                'name'     => 'Administrador',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'operador@finor.edu.bo'],
            [
                'name'     => 'Operador General',
                'password' => Hash::make('password'),
                'role'     => 'operador',
            ]
        );

        User::whereNotIn('role', ['admin', 'operador'])->update(['role' => 'operador']);

        // ─── CURSO ───────────────────────────────────────────────────────
        $curso = Curso::updateOrCreate(
            ['nombre' => 'Maestria en Educacion Superior', 'version' => 4, 'edicion' => 2],
            [
                'tipo'                       => 'Maestría',
                'periodo'                    => '2026-1',
                'costo_matricula'            => 500,
                'costo_total_estudio'        => 12500,
                'costo_defensa_diplomado'    => 500,
                'costo_defensa_especialidad' => 700,
                'costo_defensa_maestria'     => 4000,
                'nro_modulos_diplomado'      => 5,
                'nro_modulos_especialidad'   => 6,
                'nro_modulos_maestria'       => 5,
                'cupo'                       => 30,
                'activo'                     => true,
            ]
        );

        $estudiantes = [
            [
                'registro'             => '219091846',
                'nombres'              => 'William Ezequiel',
                'paterno'              => 'Olivera',
                'materno'              => null,
                'cedula'               => '17749814',
                'celular'              => '72118349',
                'descuento_porcentaje' => 50,
                'comprobante_matricula'=> '21649751',
            ],
            [
                'registro'             => '220045362',
                'nombres'              => 'Carlos Manuel',
                'paterno'              => 'Ferrel',
                'materno'              => 'Escobar',
                'cedula'               => '15796548',
                'celular'              => '69058761',
                'descuento_porcentaje' => 50,
                'comprobante_matricula'=> '24631592',
            ],
            [
                'registro'             => '221245781',
                'nombres'              => 'Carlos Daniel',
                'paterno'              => 'Cortez',
                'materno'              => 'Barco',
                'cedula'               => '18597584',
                'celular'              => '71085649',
                'descuento_porcentaje' => 50,
                'comprobante_matricula'=> '20548921',
            ],
            [
                'registro'             => '221292346',
                'nombres'              => 'Rodrigo',
                'paterno'              => 'Parada',
                'materno'              => 'Bazan',
                'cedula'               => '15187469',
                'celular'              => '68830260',
                'descuento_porcentaje' => 0,
                'comprobante_matricula'=> '25862489',
            ],
        ];

        $nroModulosD = $curso->nro_modulos_diplomado;
        $nroModulosE = $curso->nro_modulos_especialidad ?? 0;
        $nroModulosM = $curso->nro_modulos_maestria ?? 0;

        foreach ($estudiantes as $data) {
            $comprobante = $data['comprobante_matricula'];
            unset($data['comprobante_matricula']);

            $estudiante = Estudiante::firstOrCreate(
                ['registro' => $data['registro']],
                array_merge($data, ['observaciones' => null])
            );

            $inscripcion = Inscripcion::firstOrCreate(
                ['estudiante_id' => $estudiante->id, 'curso_id' => $curso->id],
                [
                    'tipo_inscripcion'  => 'Maestría',
                    'fecha_inscripcion' => '2026-04-21',
                    'estado_academico'  => 'Activo',
                    'estado_financiero' => 'Parcial',
                    'modalidad_pago'    => 'Cuotas',
                ]
            );

            $descuento = $estudiante->descuento_porcentaje / 100;
            $factorDescuento = 1 - $descuento;

            $detallePlan = [];
            $nroCuota = 0;

            // 1. Matrícula
            $nroCuota++;
            $montoMatricula = $curso->costo_matricula;
            $detallePlan[] = [
                'nro_cuota' => $nroCuota, 'nro_modulo' => 0,
                'concepto' => 'Matrícula', 'fase' => 'Matrícula',
                'monto_programado' => $montoMatricula,
                'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $montoMatricula,
                'fecha_vencimiento' => '2026-04-21', 'estado' => 'Pendiente',
            ];

            // 2. Módulos Diplomado
            $totalModulos = $nroModulosD + $nroModulosE + $nroModulosM;
            $acumulado = 0;
            $costoModuloBase = $totalModulos > 0 ? $curso->costo_total_estudio / $totalModulos : 0;

            for ($i = 1; $i <= $nroModulosD; $i++) {
                $nroCuota++;
                $monto = $i < $nroModulosD
                    ? $costoModuloBase * $factorDescuento
                    : $curso->costo_total_estudio * $factorDescuento - $acumulado
                      - ($nroModulosE + $nroModulosM) * $costoModuloBase * $factorDescuento;
                $monto = round($monto, 2);
                $acumulado += $costoModuloBase * $factorDescuento;
                $detallePlan[] = [
                    'nro_cuota' => $nroCuota, 'nro_modulo' => $i,
                    'concepto' => 'Módulo ' . $i, 'fase' => 'Diplomado',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($nroCuota)->format('Y-m-d'), 'estado' => 'Pendiente',
                ];
            }

            // 3. Defensa Diplomado
            if ($curso->costo_defensa_diplomado > 0) {
                $nroCuota++;
                $monto = $curso->costo_defensa_diplomado;
                $detallePlan[] = [
                    'nro_cuota' => $nroCuota, 'nro_modulo' => null,
                    'concepto' => 'Defensa Diplomado', 'fase' => 'Defensa',
                    'monto_programado' => $monto,
                    'monto_pagado' => 0, 'monto_descuento' => 0, 'saldo_cuota' => $monto,
                    'fecha_vencimiento' => now()->addMonths($nroCuota)->format('Y-m-d'), 'estado' => 'Pendiente',
                ];
            }

            // 4. Módulos Especialidad
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
                    'fecha_vencimiento' => now()->addMonths($nroCuota)->format('Y-m-d'), 'estado' => 'Pendiente',
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
                    'fecha_vencimiento' => now()->addMonths($nroCuota)->format('Y-m-d'), 'estado' => 'Pendiente',
                ];
            }

            // 5. Módulos Maestría
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
                    'fecha_vencimiento' => now()->addMonths($nroCuota)->format('Y-m-d'), 'estado' => 'Pendiente',
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
                    'fecha_vencimiento' => now()->addMonths($nroCuota)->format('Y-m-d'), 'estado' => 'Pendiente',
                ];
            }

            $montoTotalProgramado = collect($detallePlan)->sum('monto_programado');

            $planPago = PlanPago::firstOrCreate(
                ['inscripcion_id' => $inscripcion->id],
                [
                    'monto_total_programado' => $montoTotalProgramado,
                    'monto_total_pagado'     => 0,
                    'saldo_pendiente'        => $montoTotalProgramado,
                    'total_cuotas'           => count($detallePlan),
                    'estado'                 => 'Pendiente',
                ]
            );

            $detalleCreado = false;
            foreach ($detallePlan as $det) {
                $detalle = DetallePlanPago::firstOrCreate(
                    ['plan_pago_id' => $planPago->id, 'nro_cuota' => $det['nro_cuota']],
                    $det
                );
                if ($det['concepto'] === 'Matrícula') {
                    $detalleCreado = $detalle;
                }
            }

            if ($detalleCreado && Pago::where('detalle_plan_pago_id', $detalleCreado->id)->count() === 0) {
                Pago::create([
                    'detalle_plan_pago_id' => $detalleCreado->id,
                    'inscripcion_id'       => $inscripcion->id,
                    'fecha_pago'           => '2026-04-21',
                    'monto'                => $montoMatricula,
                    'nro_comprobante'      => $comprobante,
                    'observacion'          => 'Pago de matrícula inicial',
                ]);

                $detalleCreado->update([
                    'monto_pagado' => $montoMatricula,
                    'saldo_cuota'  => 0,
                    'estado'       => 'Pagado',
                ]);

                $planPago->update([
                    'monto_total_pagado' => $montoMatricula,
                    'saldo_pendiente'    => $montoTotalProgramado - $montoMatricula,
                    'estado'             => 'Parcial',
                ]);

                $inscripcion->update(['estado_financiero' => 'Parcial']);
            }
        }

        $this->command->info('✅ Seeder ejecutado:');
        $this->command->info('   • 2 usuarios (admin + operador)');
        $this->command->info('   • 1 curso (Maestría en Educación Superior)');
        $this->command->info('   • 4 estudiantes inscritos con cronogramas completos');
    }
}