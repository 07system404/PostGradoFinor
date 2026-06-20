@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">

{{-- ════════════ SECCIÓN 1: TARJETAS DE MÉTRICAS ════════════ --}}
<div class="dash-metricas">

    {{-- Total Alumnos --}}
    <div class="metrica-card metrica-azul">
        <div class="metrica-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="metrica-info">
            <span class="metrica-label">Total Alumnos</span>
            <span class="metrica-numero">{{ number_format($metricas['total_alumnos']) }}</span>
        </div>
    </div>

    {{-- Total Recaudado --}}
    <div class="metrica-card metrica-verde">
        <div class="metrica-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="metrica-info">
            <span class="metrica-label">Total Recaudado</span>
            <span class="metrica-numero">Bs {{ number_format($metricas['total_recaudado'], 2) }}</span>
        </div>
    </div>

    {{-- Total Pendiente --}}
    <div class="metrica-card metrica-rojo">
        <div class="metrica-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="metrica-info">
            <span class="metrica-label">Total Pendiente</span>
            <span class="metrica-numero">Bs {{ number_format($metricas['total_pendiente'], 2) }}</span>
        </div>
    </div>

    {{-- Cursos Activos --}}
    <div class="metrica-card metrica-morado">
        <div class="metrica-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
        </div>
        <div class="metrica-info">
            <span class="metrica-label">Cursos Activos</span>
            <span class="metrica-numero">{{ number_format($metricas['cursos_activos']) }}</span>
        </div>
    </div>

    {{-- Alumnos en Mora --}}
    <div class="metrica-card metrica-rojo">
        <div class="metrica-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="metrica-info">
            <span class="metrica-label">Alumnos en Mora</span>
            <span class="metrica-numero">{{ number_format($metricas['alumnos_mora']) }}</span>
        </div>
    </div>

    {{-- Nuevas Inscripciones del Mes --}}
    <div class="metrica-card metrica-naranja">
        <div class="metrica-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
                <line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
        </div>
        <div class="metrica-info">
            <span class="metrica-label">Nuevas Inscrip. del Mes</span>
            <span class="metrica-numero">{{ number_format($metricas['inscripciones_mes']) }}</span>
        </div>
    </div>

</div>

{{-- ════════════ SECCIÓN 2: GRÁFICOS ════════════ --}}
<div class="dash-graficos">

    {{-- Ingresos por mes (ancho completo) --}}
    <div class="grafico-card grafico-full">
        <div class="grafico-header">
            <h3>Ingresos por Mes</h3>
            <span class="grafico-sub">Últimos 6 meses (Bs)</span>
        </div>
        <div class="grafico-canvas-wrap">
            <canvas id="chartIngresos"></canvas>
        </div>
    </div>

    {{-- Alumnos por curso --}}
    <div class="grafico-card">
        <div class="grafico-header">
            <h3>Alumnos por Curso</h3>
            <span class="grafico-sub">Programas activos</span>
        </div>
        <div class="grafico-canvas-wrap">
            <canvas id="chartCursos"></canvas>
        </div>
    </div>

    {{-- Estado de cartera --}}
    <div class="grafico-card">
        <div class="grafico-header">
            <h3>Estado de Cartera</h3>
            <span class="grafico-sub">Distribución de alumnos</span>
        </div>
        <div class="grafico-canvas-wrap grafico-canvas-donut">
            <canvas id="chartCartera"></canvas>
        </div>
    </div>

</div>

{{-- Datos para Chart.js --}}
<script>
    window.dashboardData = {
        ingresos: {
            labels: @json($ingresosLabels),
            valores: @json($ingresosValores),
        },
        cursos: {
            labels: @json($cursosLabels),
            valores: @json($cursosValores),
        },
        cartera: {
            labels: @json($carteraLabels),
            valores: @json($carteraValores),
        },
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}?v={{ filemtime(public_path('js/dashboard.js')) }}"></script>

@endsection
