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

        // Corregir cualquier usuario con role inválido (secretario, null, etc.)
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

            $planPago = PlanPago::firstOrCreate(
                ['inscripcion_id' => $inscripcion->id],
                [
                    'monto_total_programado' => 500,
                    'monto_total_pagado'     => 0,
                    'saldo_pendiente'        => 500,
                    'total_cuotas'           => 1,
                    'estado'                 => 'Pendiente',
                ]
            );

            $detalle = DetallePlanPago::firstOrCreate(
                ['plan_pago_id' => $planPago->id, 'nro_cuota' => 1],
                [
                    'concepto'          => 'Matricula',
                    'fase'              => 'Diplomado',
                    'monto_programado'  => 500,
                    'monto_pagado'      => 0,
                    'monto_descuento'   => 0,
                    'saldo_cuota'       => 500,
                    'fecha_vencimiento' => '2026-04-21',
                    'estado'            => 'Pendiente',
                ]
            );

            if ($detalle->wasRecentlyCreated || Pago::where('detalle_plan_pago_id', $detalle->id)->count() === 0) {
                Pago::create([
                    'detalle_plan_pago_id' => $detalle->id,
                    'inscripcion_id'       => $inscripcion->id,
                    'fecha_pago'           => '2026-04-21',
                    'monto'                => 500.00,
                    'nro_comprobante'      => $comprobante,
                    'observacion'          => 'Pago de matricula inicial',
                ]);

                $detalle->update([
                    'monto_pagado' => 500,
                    'saldo_cuota'  => 0,
                    'estado'       => 'Pagado',
                ]);

                $planPago->update([
                    'monto_total_pagado' => 500,
                    'saldo_pendiente'    => 0,
                    'estado'             => 'Pagado',
                ]);

                $inscripcion->update(['estado_financiero' => 'Al Día']);
            }
        }

        $this->command->info('✅ Seeder ejecutado:');
        $this->command->info('   • 2 usuarios (admin + operador)');
        $this->command->info('   • 1 curso (Maestría en Educación Superior)');
        $this->command->info('   • 4 estudiantes inscritos con pagos registrados');
        $this->command->info('   • Roles corregidos: solo admin y operador válidos');
    }
}