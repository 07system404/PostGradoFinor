@extends('layouts.app')

@section('title', 'Caja y Facturación - PostGrado Pro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/caja_index.css') }}">
@endpush

@section('content')

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<div class="finanzas-layout">
    <!-- Panel Izquierdo: Lista de Alumnos -->
    <div class="panel-alumnos">
        <div class="panel-alumnos-header">
            <form id="form-buscar-finanzas" action="{{ route('caja.index') }}" method="GET" class="busqueda-finanzas">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/>
                    <line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <input type="text" name="buscar" id="buscar-alumno-finanzas" placeholder="Filtrar por nombre o ID" value="{{ request('buscar') }}">
            </form>
        </div>

        <div class="lista-alumnos">
            @forelse($estudiantesFinanciero as $est)
                @php
                    $claseBadge = match($est->estado_financiero_label) {
                        'Al Día' => 'badge-al-dia',
                        'En Mora' => 'badge-en-mora',
                        'Sin Pagar' => 'badge-sin-pagar',
                        'Parcial' => 'badge-parcial',
                        default => 'badge-sin-pagar',
                    };
                    $isActive = $estudianteSeleccionado && $estudianteSeleccionado->id == $est->id;
                @endphp
                <a href="{{ route('caja.index', array_merge(request()->all(), ['estudiante_id' => $est->id])) }}" 
                   class="alumno-item {{ $isActive ? 'active' : '' }}">
                    <span class="alumno-nombre">{{ $est->nombre_completo }}</span>
                    <span class="badge-financiero {{ $claseBadge }}">{{ strtoupper($est->estado_financiero_label) }}</span>
                </a>
            @empty
                <div style="padding: 20px; text-align: center; color: var(--gray-400); font-size: 14px;">
                    No se encontraron alumnos.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Panel Derecho: Detalle de Cuenta -->
    <div class="panel-detalle">
        @if($estudianteSeleccionado && $inscripcionSeleccionada && $planPago)

            <!-- Header del detalle -->
            <div class="detalle-header">
                <h2>Detalle de Cuenta: {{ $estudianteSeleccionado->nombre_completo }}</h2>
            </div>

            <div class="curso-meta-grid">
                <div class="curso-meta-card">
                    <span>Programa</span>
                    <strong>{{ $inscripcionSeleccionada->curso->nombre }} ({{ $inscripcionSeleccionada->tipo_inscripcion }})</strong>
                </div>
                <div class="curso-meta-card">
                    <span>Módulos</span>
                    <strong>{{ $inscripcionSeleccionada->curso->getNroModulosForTipo($inscripcionSeleccionada->tipo_inscripcion) }}</strong>
                </div>
                <div class="curso-meta-card">
                    <span>Costo Defensa</span>
                    <strong>Bs {{ number_format($inscripcionSeleccionada->curso->getCostoDefensaForTipo($inscripcionSeleccionada->tipo_inscripcion), 2) }}</strong>
                </div>
            </div>

            <!-- Cards de Resumen -->
            <div class="resumen-cards">
                <div class="resumen-card">
                    <div class="resumen-label">Monto Total Programa</div>
                    <div class="resumen-monto">Bs {{ number_format($planPago->monto_total_programado, 2) }}</div>
                    <div class="resumen-nota">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Incluye matrícula y tasas
                    </div>
                </div>

                <div class="resumen-card">
                    <div class="resumen-label">Total Pagado</div>
                    <div class="resumen-monto">Bs {{ number_format($planPago->monto_total_pagado, 2) }}</div>
                    <div class="progress-bar-container">
                        @php
                            $porcentaje = $planPago->monto_total_programado > 0 
                                ? ($planPago->monto_total_pagado / $planPago->monto_total_programado) * 100 
                                : 0;
                        @endphp
                        <div class="progress-bar-fill" style="width: {{ min($porcentaje, 100) }}%;"></div>
                    </div>
                </div>

                <div class="resumen-card {{ $planPago->saldo_pendiente > 0 ? 'alert' : '' }}">
                    <div class="resumen-label">Saldo Pendiente</div>
                    <div class="resumen-monto">Bs {{ number_format($planPago->saldo_pendiente, 2) }}</div>
                    @php
                        $vencido = $detalles->filter(function($d) {
                            return ($d->estado === 'Vencido') || 
                                   ($d->estado === 'Pendiente' && $d->fecha_vencimiento && $d->fecha_vencimiento < now());
                        })->sum('monto_programado');
                    @endphp
                    @if($vencido > 0)
                    <div class="resumen-nota">
                        Vencido: Bs {{ number_format($vencido, 2) }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Cronograma de Pagos -->
            <div class="cronograma-card">
                <div class="cronograma-header">
                    <div class="cronograma-titulo">
                        <select class="select-programa" id="select-programa">
                            @foreach($estudianteSeleccionado->inscripciones as $insc)
                            <option value="{{ $insc->id }}" {{ $inscripcionSeleccionada->id == $insc->id ? 'selected' : '' }}>
                                {{ strtoupper($insc->curso->nombre) }}
                            </option>
                            @endforeach
                        </select>
                        <h3>Cronograma de Pagos</h3>
                    </div>
                    <div class="resumen-cuotas">
                        @php
                            $pagados = $detalles->whereIn('estado', ['Pagado', 'Parcial'])->count();
                            $vencidos = $detalles->filter(function($d) {
                                return ($d->estado === 'Vencido') || 
                                       ($d->estado === 'Pendiente' && $d->fecha_vencimiento && $d->fecha_vencimiento < now());
                            })->count();
                        @endphp
                        <span class="badge-cuotas badge-pagados">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--success); display: inline-block;"></span>
                            {{ $pagados }} Pagados
                        </span>
                        <span class="badge-cuotas badge-vencidos">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--danger); display: inline-block;"></span>
                            {{ $vencidos }} Vencido{{ $vencidos != 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <table class="cronograma-table">
                    <thead>
                        <tr>
                            <th>CONCEPTO / MÓDULO</th>
                            <th>F. VENCIMIENTO</th>
                            <th>MONTO</th>
                            <th>ESTADO</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detalles as $detalle)
                            @php
                                $isVencido = ($detalle->estado === 'Vencido') || 
                                            ($detalle->estado === 'Pendiente' && $detalle->fecha_vencimiento && $detalle->fecha_vencimiento < now());

                                $claseEstado = match($detalle->estado) {
                                    'Pagado' => 'estado-cuota-pagado',
                                    'Vencido' => 'estado-cuota-vencido',
                                    'Pendiente' => $isVencido ? 'estado-cuota-vencido' : 'estado-cuota-pendiente',
                                    'Parcial' => 'estado-cuota-pendiente',
                                    default => 'estado-cuota-programado',
                                };

                                $textoEstado = match($detalle->estado) {
                                    'Pagado' => 'Pagado',
                                    'Vencido' => 'Vencido',
                                    'Pendiente' => $isVencido ? 'Vencido' : 'Pendiente',
                                    'Parcial' => 'Pendiente',
                                    default => 'Programado',
                                };

                                $iconoEstado = match($textoEstado) {
                                    'Pagado' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>',
                                    'Vencido' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                                    'Pendiente' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                                    default => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
                                };
                            @endphp
                        <tr class="{{ $isVencido ? 'fila-vencida' : '' }}">
                            <td>
                                <span class="concepto-cuota">{{ $detalle->concepto }}</span>
                            </td>
                            <td>
                                <span class="fecha-vencimiento">
                                    {{ $detalle->fecha_vencimiento ? $detalle->fecha_vencimiento->format('d M, Y') : '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="monto-cuota">Bs {{ number_format($detalle->monto_programado, 2) }}</span>
                            </td>
                            <td>
                                <span class="{{ $claseEstado }}">
                                    {!! $iconoEstado !!}
                                    {{ $textoEstado }}
                                </span>
                            </td>
                            <td>
                                @if($detalle->estado === 'Pagado')
                                    <a href="#" class="btn-ver-pago">Ver</a>
                                @elseif($isVencido || $detalle->estado === 'Pendiente' || $detalle->estado === 'Parcial')
                                    <a href="{{ route('caja.pago.formulario', $detalle->id) }}" class="btn-cobrar">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                                            <line x1="2" y1="10" x2="22" y2="10"/>
                                        </svg>
                                        Cobrar
                                    </a>
                                @else
                                    <span class="texto-no-disponible">No disponible</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div class="sin-seleccion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                    <line x1="6" y1="15" x2="6.01" y2="15"/>
                    <line x1="10" y1="15" x2="10.01" y2="15"/>
                </svg>
                <h3>Seleccione un alumno</h3>
                <p>Seleccione un alumno de la lista para ver el detalle de su cuenta y cronograma de pagos.</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/caja_index.js') }}"></script>
@endpush
