@extends('layouts.app')

@section('title', 'Recibo de Pago - PostGrado Pro')

@push('styles')
<link rel="stylesheet" href="/css/caja.css?v={{ filemtime(public_path('css/caja.css')) }}">
@endpush

@section('content')


@php
    $totalPagado = $pagos->sum('monto');
    $saldoCuota  = $detalle->monto_programado - $detalle->monto_pagado;
@endphp

<!-- Encabezado -->
<div class="recibo-header">
    <div class="recibo-breadcrumb">
        <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion->id]) }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Volver a Finanzas
        </a>
    </div>
    <div class="recibo-titulo-row">
        <div>
            <h2 class="recibo-titulo">Recibo de Pago</h2>
            <p class="recibo-subtitulo">{{ $detalle->concepto }} — {{ $estudiante->nombre_completo }}</p>
        </div>
        @php
            $claseEstado = match($detalle->estado) {
                'Pagado' => 'recibo-badge-pagado',
                'Parcial' => 'recibo-badge-parcial',
                default => 'recibo-badge-pendiente',
            };
        @endphp
        <span class="recibo-badge {{ $claseEstado }}">{{ strtoupper($detalle->estado) }}</span>
    </div>
</div>

<!-- Datos de la cuota -->
<div class="recibo-meta-grid">
    <div class="recibo-meta-card">
        <span>Alumno</span>
        <strong>{{ $estudiante->nombre_completo }}</strong>
    </div>
    <div class="recibo-meta-card">
        <span>Programa</span>
        <strong>{{ $inscripcion->curso->nombre }} / {{ $inscripcion->tipo_inscripcion }}</strong>
    </div>
    <div class="recibo-meta-card">
        <span>Monto de la cuota</span>
        <strong>Bs {{ number_format($detalle->monto_programado, 2) }}</strong>
    </div>
    <div class="recibo-meta-card">
        <span>Total pagado</span>
        <strong class="texto-pagado">Bs {{ number_format($totalPagado, 2) }}</strong>
    </div>
    <div class="recibo-meta-card">
        <span>Saldo pendiente</span>
        <strong class="{{ $saldoCuota > 0 ? 'texto-deuda' : 'texto-pagado' }}">Bs {{ number_format(max($saldoCuota, 0), 2) }}</strong>
    </div>
</div>

<!-- Lista de pagos realizados -->
@forelse($pagos as $pago)
    @php
        $ext = $pago->archivo_adjunto ? strtolower(pathinfo($pago->archivo_adjunto, PATHINFO_EXTENSION)) : null;
        $url = $pago->archivo_adjunto ? '/storage/' . $pago->archivo_adjunto : null;
        $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $esPdf = $ext === 'pdf';
    @endphp
    <div class="recibo-pago-card">
        <div class="recibo-pago-info">
            <div class="recibo-pago-titulo">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Pago registrado
            </div>
            <div class="recibo-datos">
                <div class="recibo-dato">
                    <span>Monto pagado</span>
                    <strong class="texto-pagado">Bs {{ number_format($pago->monto, 2) }}</strong>
                </div>
                <div class="recibo-dato">
                    <span>Fecha de pago</span>
                    <strong>{{ $pago->fecha_pago->format('d M, Y') }}</strong>
                </div>
                <div class="recibo-dato">
                    <span>N° de comprobante</span>
                    <strong>{{ $pago->nro_comprobante }}</strong>
                </div>
            </div>
            @if($pago->observacion)
                <div class="recibo-observacion">
                    <span>Observación</span>
                    <p>{{ $pago->observacion }}</p>
                </div>
            @endif
        </div>

        <!-- Comprobante adjunto -->
        <div class="recibo-comprobante">
            <span class="recibo-comprobante-label">Comprobante digital</span>
            @if($url && $esImagen)
                <a href="{{ $url }}" target="_blank" class="recibo-comprobante-preview">
                    <img src="{{ $url }}" alt="Comprobante de pago">
                </a>
                <a href="{{ $url }}" target="_blank" class="recibo-btn-archivo">Ver / Descargar imagen</a>
            @elseif($url && $esPdf)
                <embed src="{{ $url }}" type="application/pdf" class="recibo-comprobante-pdf">
                <a href="{{ $url }}" target="_blank" class="recibo-btn-archivo">Abrir PDF en pestaña nueva</a>
            @elseif($url)
                <a href="{{ $url }}" target="_blank" class="recibo-btn-archivo">Descargar archivo adjunto</a>
            @else
                <div class="recibo-sin-archivo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>No se adjuntó comprobante digital para este pago.</span>
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="recibo-vacio">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <h3>Sin pagos registrados</h3>
        <p>Esta cuota todavía no tiene ningún pago registrado.</p>
        <a href="{{ route('caja.pago.formulario', $detalle->id) }}" class="recibo-btn-cobrar">Registrar Pago</a>
    </div>
@endforelse

@endsection
