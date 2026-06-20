@extends('layouts.app')

@section('title', 'Detalle de Cuenta')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/caja_show.css') }}">
@endpush

@section('content')
<div class="caja-detalle">
    <div class="breadcrumb">
        <a href="{{ route('caja.index') }}">Caja y Finanzas</a> / Detalle de Cuenta
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <h2>Detalle de Cuenta: {{ $estudiante->nombres }} {{ $estudiante->paterno }}</h2>

    @foreach($inscripciones as $inscripcion)
        @php
            $plan = $inscripcion->planPago;
            if (!$plan) continue;
            $totalProgramado = $plan->monto_total_programado;
            $totalPagado = $plan->monto_total_pagado;
            $saldoPendiente = $plan->saldo_pendiente;
            // Calcular vencido (suma de cuotas vencidas no pagadas)
            $vencido = $plan->detalles->filter(function ($det) {
                return $det->fecha_vencimiento && $det->fecha_vencimiento < now() && $det->estado != 'Pagado';
            })->sum('saldo_cuota');
        @endphp
        <div class="resumen-cuenta">
            <h3>{{ $inscripcion->curso->nombre }}</h3>
            <div class="resumen-cards">
                <div class="resumen-card">
                    <span class="label">MONTO TOTAL PROGRAMA</span>
                    <span class="value">${{ number_format($totalProgramado, 2) }}</span>
                    <span class="sub">Incluye matrícula y tasas</span>
                </div>
                <div class="resumen-card">
                    <span class="label">TOTAL PAGADO</span>
                    <span class="value">${{ number_format($totalPagado, 2) }}</span>
                </div>
                <div class="resumen-card">
                    <span class="label">SALDO PENDIENTE</span>
                    <span class="value">${{ number_format($saldoPendiente, 2) }}</span>
                    @if($vencido > 0)
                        <span class="sub vencido">Vencido: ${{ number_format($vencido, 2) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="cronograma">
            <h4>Cronograma de Pagos</h4>
            <table class="table-pagos">
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
                    @foreach($plan->detalles as $detalle)
                        @php
                            $estado = $detalle->estado;
                            $icono = '';
                            if ($estado == 'Pagado') $icono = '✓ PAGADO';
                            elseif ($estado == 'Vencido') $icono = '✓ VENCIDO';
                            elseif ($estado == 'Pendiente') $icono = '✓ PENDIENTE';
                            elseif ($estado == 'Parcial') $icono = '✓ PARCIAL';
                            else $icono = 'PROGRAMADO';
                        @endphp
                        <tr>
                            <td>{{ $detalle->concepto }}</td>
                            <td>{{ $detalle->fecha_vencimiento ? \Carbon\Carbon::parse($detalle->fecha_vencimiento)->format('d M, Y') : '-' }}</td>
                            <td>${{ number_format($detalle->monto_programado, 2) }}</td>
                            <td class="estado {{ strtolower($estado) }}">{{ $icono }}</td>
                            <td>
                                @if($estado != 'Pagado')
                                    <a href="{{ route('caja.pago.create', ['estudiante' => $estudiante, 'detalle' => $detalle]) }}" class="btn-pagar">Registrar Pago</a>
                                @else
                                    <span class="btn-recibo">Ver Recibo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/caja_show.js') }}"></script>
@endpush