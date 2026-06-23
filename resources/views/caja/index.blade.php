@extends('layouts.app')

@section('title', 'Caja y Facturación')

@push('styles')
<link rel="stylesheet" href="/css/caja.css?v={{ filemtime(public_path('css/caja.css')) }}">
<link rel="stylesheet" href="/css/form-inscripcion-programa.css?v={{ filemtime(public_path('css/form-inscripcion-programa.css')) }}">
@endpush

@section('content')


<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<!-- Tarjetas de Resumen Global + Botón Pago -->
<div class="resumen-global">
    <div class="resumen-global-card resumen-pagado">
        <div class="resumen-global-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <div class="resumen-global-info">
            <span class="resumen-global-label">Total Pago Realizado</span>
            <span class="resumen-global-monto">Bs {{ number_format($totalPagadoGlobal, 2) }}</span>
        </div>
    </div>
    <div class="resumen-global-card resumen-deuda">
        <div class="resumen-global-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="resumen-global-info">
            <span class="resumen-global-label">Total Deuda Pendiente</span>
            <span class="resumen-global-monto">Bs {{ number_format($totalDeudaGlobal, 2) }}</span>
        </div>
    </div>
    <a href="{{ route('caja.pago.formulario') }}" class="resumen-global-btn-pago" id="btn-ir-pago">
        <div class="btn-pago-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="2"/>
                <line x1="2" y1="10" x2="22" y2="10"/>
            </svg>
        </div>
        <span class="btn-pago-texto">Registrar</span>
        <span class="btn-pago-subtexto">Pago</span>
    </a>
</div>

<div class="finanzas-layout">
    <!-- Panel Izquierdo: Lista de Alumnos -->
    <div class="panel-alumnos">
        <div class="panel-alumnos-header">
            <div class="busqueda-finanzas">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="buscar-alumno-finanzas" placeholder="Buscar por nombre, cédula, registro...">
            </div>
        </div>

        <div class="lista-alumnos">
            @forelse($estudiantesFinanciero as $est)
                @php
                    $claseBadge = match($est->estado_financiero_label) {
                        'Al Día' => 'badge-al-dia',
                        'En Mora' => 'badge-en-mora',
                        'Matrícula Pendiente' => 'badge-matricula-pendiente',
                        'Sin Inscribir' => 'badge-sin-inscribir',
                        default => 'badge-sin-inscribir',
                    };
                    $isActive = $estudianteSeleccionado && $estudianteSeleccionado->id == $est->id;
                @endphp
                <a href="{{ route('caja.index', array_merge(request()->all(), ['estudiante_id' => $est->id])) }}" 
                   class="alumno-item {{ $isActive ? 'active' : '' }}"
                   data-nombre="{{ strtolower($est->nombre_completo) }}"
                   data-cedula="{{ strtolower($est->cedula ?? '') }}"
                   data-registro="{{ strtolower($est->registro ?? '') }}">
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

    <!-- Panel Derecho: Detalle de Cuenta / Modal Inscripción -->
    <div class="panel-detalle">
        @if($estudianteSeleccionado && $mostrarModalInscripcion)

            <x-form-inscripcion-programa :alumno-id="$estudianteSeleccionado->id" />

            <div class="sin-seleccion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <line x1="2" y1="10" x2="22" y2="10"/>
                    <line x1="6" y1="15" x2="6.01" y2="15"/>
                    <line x1="10" y1="15" x2="10.01" y2="15"/>
                </svg>
                <h3>Alumno sin inscripción</h3>
                <p>Este alumno no tiene ningún programa inscrito. Haga clic en el botón superior para inscribirlo.</p>
                <button type="button" class="btn-inscribir-caja" id="btn-nueva-inscripcion">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Nueva Inscripción
                </button>
            </div>

        @elseif($estudianteSeleccionado && $inscripcionSeleccionada && $planPago)

            <!-- Header del detalle -->
            <div class="detalle-header">
                <h2>Detalle de Cuenta: {{ $estudianteSeleccionado->nombre_completo }}</h2>
            </div>

            <div class="curso-meta-grid dos-columnas">
                <div class="curso-meta-card">
                    <span>Programa</span>
                    <strong>{{ $inscripcionSeleccionada->curso->nombre }} ({{ $inscripcionSeleccionada->tipo_inscripcion }})</strong>
                </div>
            </div>

            <!-- Cards de Resumen -->
            <div class="resumen-cards">
                <div class="resumen-card">
                    <div class="resumen-label">Monto Total Programa</div>
                    <div class="resumen-monto">Bs {{ number_format($montoProgramadoActivo ?? $planPago->monto_total_programado, 2) }}</div>
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
                            $baseProgramado = $montoProgramadoActivo ?? $planPago->monto_total_programado;
                            $porcentaje = $baseProgramado > 0 
                                ? ($planPago->monto_total_pagado / $baseProgramado) * 100 
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
                        <a href="{{ route('caja.cronograma.pdf', [$estudianteSeleccionado->id, $inscripcionSeleccionada->id]) }}" 
                           class="btn-exportar-pdf" target="_blank" title="Exportar PDF">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            PDF
                        </a>
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
                                    'Condonado' => 'estado-cuota-pagado',
                                    default => 'estado-cuota-programado',
                                };

                                $textoEstado = match($detalle->estado) {
                                    'Pagado' => 'Pagado',
                                    'Vencido' => 'Vencido',
                                    'Pendiente' => $isVencido ? 'Vencido' : 'Pendiente',
                                    'Parcial' => 'Pendiente',
                                    'Condonado' => 'Condonado',
                                    default => 'Programado',
                                };

                                $iconoEstado = match($textoEstado) {
                                    'Pagado' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>',
                                    'Condonado' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
                                    'Vencido' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                                    'Pendiente' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                                    default => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
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
                                    <a href="{{ route('caja.pago.recibo', $detalle->id) }}" class="btn-ver-pago">Ver recibo</a>
                                @elseif($detalle->monto_pagado > 0)
                                    <a href="{{ route('caja.pago.recibo', $detalle->id) }}" class="btn-ver-pago">Ver pagos</a>
                                    <a href="{{ route('caja.pago.formulario', $detalle->id) }}" class="btn-cobrar">Cobrar</a>
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

<script src="/js/caja.js?v={{ filemtime(public_path('js/caja.js')) }}"></script>
<script src="/js/form-inscripcion-programa.js"></script>

@endsection
