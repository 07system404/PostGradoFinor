@extends('layouts.app')

@section('title', 'Reportes - PostGrado Pro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
@endpush

@section('content')

<div class="page-header">
    <h1>Reportes</h1>
    <p>Exporte datos del sistema en formato CSV.</p>
</div>

<div class="reportes-grid">
    <a href="{{ route('reportes.estudiantes') }}" class="reporte-card">
        <div class="reporte-icon blue">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="reporte-info">
            <h3>Listado de Alumnos</h3>
            <p>Exportar todos los estudiantes inscritos con su estado académico y financiero.</p>
        </div>
        <div class="reporte-action">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span>Descargar CSV</span>
        </div>
    </a>

    <a href="{{ route('reportes.programas') }}" class="reporte-card">
        <div class="reporte-icon green">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
        </div>
        <div class="reporte-info">
            <h3>Programas Académicos</h3>
            <p>Exportar listado de programas con cupos, costos y estado.</p>
        </div>
        <div class="reporte-action">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span>Descargar CSV</span>
        </div>
    </a>
</div>

@endsection
