@extends('layouts.app')

@section('title', 'Estudiantes del Programa - PostGrado Pro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cursos_show.css') }}">
@endpush

@section('content')

<!-- Breadcrumb -->
<nav class="breadcrumb-perfil" style="margin-bottom: 16px;">
    <a href="{{ route('programas.index') }}">Programas Académicos</a>
    <span class="breadcrumb-sep">›</span>
    <span class="active">{{ $programa->nombre }}</span>
</nav>

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<!-- Cabecera del Curso -->
<div class="curso-show-header">
    <div class="curso-show-header-left">
        <div class="curso-show-tipo-badge tipo-{{ strtolower($programa->tipo) }}">
            {{ $programa->tipo }}
        </div>
        <h1 class="curso-show-title">{{ $programa->nombre }}</h1>
        <div class="curso-show-meta">
            <span class="curso-show-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="4 7 4 4 20 4 20 7"/>
                    <line x1="9" y1="20" x2="15" y2="20"/>
                    <line x1="12" y1="4" x2="12" y2="20"/>
                </svg>
                V{{ $programa->version }} · Edición {{ $programa->edicion }}
            </span>
            <span class="curso-show-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Periodo: {{ $programa->periodo }}
            </span>
            <span class="curso-show-meta-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                {{ $programa->inscripciones->count() }} / {{ $programa->cupo }} Alumnos
            </span>
        </div>
    </div>
    <!-- Sin botones - cabecera limpia -->
</div>

<!-- Tabla de Estudiantes Inscritos -->
<div class="curso-show-table-card" style="margin-top: 0;">
    <div class="curso-show-table-header">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Lista de Estudiantes Inscritos
        </h2>
        <span class="curso-show-count">{{ $programa->inscripciones->count() }} estudiantes</span>
    </div>

    <table class="curso-show-table">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>Registro</th>
                <th>Cédula</th>
                <th>Estado Académico</th>
            </tr>
        </thead>
        <tbody>
            @forelse($programa->inscripciones as $inscripcion)
                @php
                    $estudiante = $inscripcion->estudiante;
                    $claseEstado = match($inscripcion->estado_academico) {
                        'Activo' => 'estado-alumno-activo',
                        'Pendiente' => 'estado-alumno-pendiente',
                        'Egresado' => 'estado-alumno-egresado',
                        'Retirado' => 'estado-alumno-retirado',
                        'Congelado' => 'estado-alumno-congelado',
                        default => 'estado-alumno-pendiente',
                    };
                    $iconoEstado = match($inscripcion->estado_academico) {
                        'Activo' => 'check',
                        'Pendiente' => 'clock',
                        'Egresado' => 'check-double',
                        'Retirado' => 'x',
                        'Congelado' => 'pause',
                        default => 'clock',
                    };
                @endphp
            <tr>
                <td>
                    <div class="alumno-info">
                        <div class="alumno-avatar">
                            {{ strtoupper(substr($estudiante->nombres, 0, 1)) }}{{ strtoupper(substr($estudiante->paterno, 0, 1)) }}
                        </div>
                        <div class="alumno-nombre">
                            <span class="alumno-nombre-text">{{ $estudiante->nombre_completo }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="alumno-registro">{{ $estudiante->registro }}</span>
                </td>
                <td>
                    <span class="alumno-cedula">{{ $estudiante->cedula }}</span>
                </td>
                <td>
                    <span class="alumno-estado {{ $claseEstado }}">
                        @if($iconoEstado === 'check')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        @elseif($iconoEstado === 'clock')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        @elseif($iconoEstado === 'check-double')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="18 6 9 17 4 12"/>
                            <polyline points="22 6 13 17 11 15"/>
                        </svg>
                        @elseif($iconoEstado === 'x')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                        @elseif($iconoEstado === 'pause')
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="6" y="4" width="4" height="16"/>
                            <rect x="14" y="4" width="4" height="16"/>
                        </svg>
                        @endif
                        {{ strtoupper($inscripcion->estado_academico) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="curso-show-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                    </svg>
                    <h3>No hay estudiantes inscritos</h3>
                    <p>Este programa aún no tiene alumnos registrados.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
