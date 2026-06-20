@extends('layouts.app')

@section('title', 'Programas Académicos')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cursos_index.css') }}">
@endpush

@section('content')

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success" id="flash-alert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <span>{{ session('success') }}</span>
    <button type="button" onclick="this.parentElement.remove()" class="alert-close">✕</button>
</div>
@endif
@if(session('error'))
<div class="alert alert-error" id="flash-alert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>{{ session('error') }}</span>
    <button type="button" onclick="this.parentElement.remove()" class="alert-close">✕</button>
</div>
@endif

<!-- Barra de búsqueda (fija arriba) -->
<div class="programas-search-bar">
    <form id="form-buscar-programa" action="{{ route('programas.index') }}" method="GET" class="search-bar-form">
        <div class="search-bar-input-group">
            <svg class="search-bar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="buscar" id="buscar-programa" placeholder="Buscar programas por nombre..." 
                   value="{{ request('buscar') }}">
        </div>
        <div class="search-bar-select-group">
            <svg class="search-bar-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="8" y1="12" x2="20" y2="12"/>
                <line x1="12" y1="18" x2="20" y2="18"/>
            </svg>
            <select id="filtro-estado" name="estado" class="filter-estado">
                <option value="">Todos los Estados</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
        <a href="{{ route('programas.create') }}" class="btn-nuevo-programa">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nuevo Programa
        </a>
    </form>
</div>

<!-- Rectángulo contenedor del grid con scroll independiente -->
<div class="programas-grid-container" id="programas-grid-container"
     data-search-url="{{ route('programas.buscar.ajax') }}">
    <div class="programas-grid">
    @forelse($programas as $programa)
        @php
            $facultad = match($programa->tipo) {
                'Doctorado' => 'Facultad de Postgrado de Ingeniería',
                'Maestría' => 'Escuela Superior de Negocios',
                default => 'Departamento de Educación Continua',
            };
            
            $porcentajeOcupado = $programa->cupo > 0 ? round(($programa->inscripciones_count / $programa->cupo) * 100) : 0;
        @endphp
        <div class="programa-card">
            <!-- Card Header: Tipo badge + Estado badge -->
            <div class="card-header-top">
                <span class="card-tipo-badge tipo-{{ strtolower($programa->tipo) }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    {{ $programa->tipo }}
                </span>
                <span class="card-estado-badge {{ $programa->activo ? 'estado-activo' : 'estado-inactivo' }}">
                    <span class="estado-dot"></span>
                    {{ $programa->activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>

            <!-- Card Body: Nombre + Detalles -->
            <div class="card-body">
                <h3 class="card-title">{{ $programa->nombre }}</h3>
                <p class="card-facultad">{{ $facultad }}</p>

                <div class="card-details">
                    <!-- Fila: Versión + Periodo lado a lado -->
                    <div class="card-details-row">
                        <div class="card-detail-item card-detail-half">
                            <div class="detail-icon-wrapper">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="4 7 4 4 20 4 20 7"/>
                                    <line x1="9" y1="20" x2="15" y2="20"/>
                                    <line x1="12" y1="4" x2="12" y2="20"/>
                                </svg>
                            </div>
                            <div class="detail-text">
                                <span class="detail-label">Versión</span>
                                <span class="detail-value">{{ $programa->version }} · Ed. {{ $programa->edicion }}</span>
                            </div>
                        </div>
                        <div class="card-detail-item card-detail-half">
                            <div class="detail-icon-wrapper">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                            </div>
                            <div class="detail-text">
                                <span class="detail-label">Periodo</span>
                                <span class="detail-value">{{ $programa->periodo }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Alumnos: 4 [progress bar] 30 en una línea -->
                    <div class="alumnos-inline-row">
                        <div class="alumnos-inline-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <span class="alumnos-inline-label">Alumnos</span>
                        <strong class="alumnos-inline-num">{{ $programa->inscripciones_count }}</strong>
                        <div class="alumnos-inline-track">
                            <div class="alumnos-inline-fill" style="width: {{ min($porcentajeOcupado, 100) }}%;"></div>
                        </div>
                        <span class="alumnos-inline-cupo">{{ $programa->cupo }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Footer: Iconos de acción -->
            <div class="card-footer-icons">
                <a href="{{ route('programas.show', $programa->id) }}" class="btn-icon-action btn-icon-view" title="Ver estudiantes inscritos">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </a>
                <a href="{{ route('programas.edit', $programa->id) }}" class="btn-icon-action btn-icon-edit" title="Editar programa">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </a>
                <form action="{{ route('programas.inactivar', $programa->id) }}" method="POST" class="btn-icon-form">
                    @csrf
                    @method('PUT')
                    @if($programa->activo)
                        <button type="submit" class="btn-icon-action btn-icon-delete btn-inactivar-prog" title="Desactivar programa">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </button>
                    @else
                        <button type="submit" class="btn-icon-action btn-icon-activate" title="Activar programa">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </button>
                    @endif
                </form>
            </div>
        </div>
    @empty
        <div class="programas-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
            <h3>No se encontraron programas</h3>
            <p>No hay programas académicos que coincidan con los filtros aplicados.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="pagination-footer-cards">
    <div class="pagination-controls-cards">
        {{ $programas->links() }}
    </div>
</div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/cursos_index.js') }}"></script>
@endpush
