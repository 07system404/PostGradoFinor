@extends('layouts.app')

@section('title', 'Registro de Pago - PostGrado Pro')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/caja_pago.css') }}">
@endpush

@section('content')

<!-- Breadcrumb -->
<div class="pago-page-header">
    <div class="pago-breadcrumb">
        <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion->id]) }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Volver a Finanzas
        </a>
    </div>
    <div class="pago-referencia">
        <div>
            <div class="pago-titulo">Registro de Pago</div>
            <div class="pago-referencia-id">
                Referencia ID: <span>{{ $referencia }}</span>
            </div>
        </div>
        <span class="badge-pendiente-validacion">Pendiente de Validación</span>
    </div>
</div>

<!-- Mensajes -->
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-error">
    <ul style="margin: 0; padding-left: 18px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Formulario de Pago -->
<form id="form-registro-pago" action="{{ route('caja.pago.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="detalle_plan_pago_id" value="{{ $detalle->id }}">
    <input type="hidden" name="inscripcion_id" value="{{ $inscripcion->id }}">

    <div class="pago-meta-grid">
        <div>
            <span>Alumno</span>
            <strong>{{ $estudiante->nombre_completo }}</strong>
        </div>
        <div>
            <span>Programa</span>
            <strong>{{ $inscripcion->curso->nombre }} / {{ $inscripcion->tipo_inscripcion }}</strong>
        </div>
        <div>
            <span>Detalle</span>
            <strong>{{ $detalle->concepto }}</strong>
        </div>
        <div>
            <span>Saldo actual</span>
            <strong>Bs {{ number_format($detalle->saldo_cuota > 0 ? $detalle->saldo_cuota : $detalle->monto_programado, 2) }}</strong>
        </div>
    </div>

    <div class="pago-form-card">
        <div class="pago-form-row">
            <div class="pago-form-group">
                <label for="monto">Monto a Pagar (Bs)</label>
                <div class="pago-input-monto">
                    <span class="prefix">Bs</span>
                    <input type="number" id="monto" name="monto" step="0.01" min="0.01" 
                           value="{{ number_format($detalle->saldo_cuota > 0 ? $detalle->saldo_cuota : $detalle->monto_programado, 2, '.', '') }}" 
                           required>
                </div>
            </div>
            <div class="pago-form-group">
                <label for="fecha_pago">Fecha de Pago</label>
                <input type="date" id="fecha_pago" name="fecha_pago" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

        <div class="pago-form-row full">
            <div class="pago-form-group">
                <label for="nro_comprobante">
                    Número de Comprobante / Recibo <span class="required">*</span>
                </label>
                <input type="text" id="nro_comprobante" name="nro_comprobante" placeholder="Ej: 001-98765432" required>
            </div>
        </div>

        <div class="pago-form-row full">
            <div class="pago-form-group">
                <label>Comprobante Digital (JPG, PNG, PDF)</label>
                <div class="pago-dropzone" id="dropzone-comprobante">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <div class="pago-dropzone-text">Arrastre el archivo aquí</div>
                    <div class="pago-dropzone-sub">O haga clic para explorar en su equipo</div>
                </div>
                <input type="file" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png,.pdf">
            </div>
        </div>

        <div class="pago-form-row full">
            <div class="pago-form-group">
                <label for="observacion">Observaciones (Opcional)</label>
                <textarea id="observacion" name="observacion" placeholder="Agregue cualquier detalle adicional sobre este pago..."></textarea>
            </div>
        </div>

        <div class="pago-form-footer">
            <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion->id]) }}" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn-confirmar-pago">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <span>Confirmar Pago</span>
            </button>
        </div>
    </div>
</form>

<!-- Info Cards -->
<div class="pago-info-grid">
    <div class="pago-info-card">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <div>
            <h4>Validación Administrativa</h4>
            <p>Su pago será revisado por el departamento financiero en un plazo de 24 a 48 horas hábiles.</p>
        </div>
    </div>
    <div class="pago-info-card">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        <div>
            <h4>Pago Seguro</h4>
            <p>Toda la información proporcionada está cifrada y protegida bajo los estándares de seguridad institucional.</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/caja_pago.js') }}"></script>
@endpush
