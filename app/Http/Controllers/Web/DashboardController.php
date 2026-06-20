<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Traits\CalculaEstadoFinanciero;

class DashboardController extends Controller
{
    use CalculaEstadoFinanciero;

    public function index()
    {
        $hoy = now()->startOfDay();

        // Recorrido único de estudiantes para:
        //   - Total Pendiente (saldo de alumnos activos)
        //   - Alumnos en Mora
        //   - Estado de Cartera (Al Día / En Mora / Matrícula Pendiente)
        $estudiantes = Estudiante::with(['inscripciones.planPago.detalles'])->get();

        $totalPendiente = 0;
        $cartera = [
            'Al Día'              => 0,
            'En Mora'             => 0,
            'Matrícula Pendiente' => 0,
        ];

        foreach ($estudiantes as $est) {
            $estado = $this->calcularEstadoFinanciero($est, $hoy);

            // El saldo pendiente solo cuenta para alumnos activos.
            if ($est->activo) {
                $totalPendiente += $estado['saldo_pendiente'];
            }

            if (isset($cartera[$estado['label']])) {
                $cartera[$estado['label']]++;
            }
        }

        // 1. Tarjetas de métricas
        $metricas = [
            'total_alumnos'        => Estudiante::count(),
            'total_recaudado'      => (float) Pago::sum('monto'),
            'total_pendiente'      => (float) $totalPendiente,
            'cursos_activos'       => Curso::where('activo', true)->count(),
            'alumnos_mora'         => $cartera['En Mora'],
            'inscripciones_mes'    => Inscripcion::whereMonth('created_at', $hoy->month)
                                                  ->whereYear('created_at', $hoy->year)
                                                  ->count(),
        ];

        // 2a. Gráfico: Ingresos por mes (últimos 6 meses)
        $ingresosLabels = [];
        $ingresosValores = [];

        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->startOfMonth()->subMonths($i);

            $total = Pago::whereYear('fecha_pago', $fecha->year)
                ->whereMonth('fecha_pago', $fecha->month)
                ->sum('monto');

            $ingresosLabels[]  = ucfirst($fecha->locale('es')->isoFormat('MMM YY'));
            $ingresosValores[] = round((float) $total, 2);
        }

        // 2b. Gráfico: Alumnos por curso (programas activos)
        $cursos = Curso::where('activo', true)
            ->withCount('inscripciones')
            ->orderByDesc('inscripciones_count')
            ->get();

        $cursosLabels  = $cursos->pluck('nombre')->all();
        $cursosValores = $cursos->pluck('inscripciones_count')->all();

        // 2c. Gráfico: Estado de cartera (donut)
        $carteraLabels  = ['Al Día', 'Mora', 'Matrícula Pendiente'];
        $carteraValores = [
            $cartera['Al Día'],
            $cartera['En Mora'],
            $cartera['Matrícula Pendiente'],
        ];

        return view('dashboard.index', [
            'metricas'        => $metricas,
            'ingresosLabels'  => $ingresosLabels,
            'ingresosValores' => $ingresosValores,
            'cursosLabels'    => $cursosLabels,
            'cursosValores'   => $cursosValores,
            'carteraLabels'   => $carteraLabels,
            'carteraValores'  => $carteraValores,
        ]);
    }
}
