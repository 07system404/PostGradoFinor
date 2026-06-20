@extends('layouts.app')

@section('title', 'Registro de Pago - PostGrado Pro')

@section('content')

<link rel="stylesheet" href="{{ asset('css/caja.css') }}?v={{ filemtime(public_path('css/caja.css')) }}">

<!-- Breadcrumb -->
<div class="pago-page-header">
    <div class="pago-breadcrumb">
        @if($detalle)
        <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion->id]) }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Volver a Finanzas
        </a>
        @else
        <a href="{{ route('caja.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Volver a Finanzas
        </a>
        @endif
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

<form id="form-registro-pago" action="{{ route('caja.pago.store') }}" method="POST" enctype="multipart/form-data"
      @if($detalle) data-prefill="true"
      data-estudiante-id="{{ $estudiante->id }}"
      data-estudiante-nombre="{{ $estudiante->nombre_completo }}"
      data-estudiante-cedula="{{ $estudiante->cedula }}"
      data-estudiante-registro="{{ $estudiante->registro }}"
      data-inscripcion-id="{{ $inscripcion->id }}"
      data-curso-nombre="{{ $inscripcion->curso->nombre }}"
      data-tipo-inscripcion="{{ $inscripcion->tipo_inscripcion }}"
      data-detalle-id="{{ $detalle->id }}"
      data-concepto="{{ $detalle->concepto }}"
      data-fase="{{ $detalle->fase }}"
      data-monto="{{ number_format($detalle->saldo_cuota > 0 ? $detalle->saldo_cuota : $detalle->monto_programado, 2, '.', '') }}"
      @endif>
    @csrf
    <input type="hidden" name="detalle_plan_pago_id" id="hidden-detalle-id" value="{{ $detalle ? $detalle->id : '' }}">
    <input type="hidden" name="inscripcion_id" id="hidden-inscripcion-id" value="{{ $detalle ? $inscripcion->id : '' }}">

    {{-- Alerta de concepto ya pagado --}}
    <div class="pago-alerta-pagado" id="pago-alerta-pagado" style="display:none;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <div>
            <strong>Concepto ya pagado</strong>
            <p id="pago-alerta-pagado-texto">Este concepto ya fue pagado anteriormente. Seleccione otro concepto para continuar.</p>
        </div>
    </div>

    {{-- Grid 2 columnas --}}
    <div class="pago-grid-2col">

        {{-- COLUMNA IZQUIERDA --}}
        <div class="pago-col-left">

            {{-- Alumno --}}
            <div class="pago-card-col">
                <div class="pago-card-col-label">Alumno</div>
                <div class="pago-autocomplete">
                    <input type="text" id="buscar-alumno-pago" placeholder="Buscar por nombre, cédula o registro..." autocomplete="off">
                    <div class="pago-autocomplete-dropdown" id="autocomplete-dropdown"></div>
                </div>
                <div class="pago-alumno-seleccionado" id="pago-alumno-seleccionado">
                    <span class="pago-sin-seleccion" id="pago-sin-alumno">Ningún alumno seleccionado</span>
                    <strong id="pago-alumno-nombre" style="display:none;"></strong>
                    <span id="pago-alumno-datos" style="display:none;"></span>
                    <button type="button" id="btn-cambiar-alumno" class="pago-btn-cambiar" style="display:none;">Cambiar</button>
                </div>
            </div>

            {{-- Programa --}}
            <div class="pago-card-col">
                <div class="pago-card-col-label">Seleccionar Curso / Programa</div>
                <select id="select-programa-pago" disabled>
                    <option value="">Seleccione un alumno primero...</option>
                </select>
            </div>

            {{-- Concepto --}}
            <div class="pago-card-col">
                <div class="pago-card-col-label">¿Qué desea pagar? <span class="required">*</span></div>
                <select id="select-categoria-pago" disabled>
                    <option value="">Seleccionar categoría...</option>
                    <option value="matricula">Matrícula</option>
                    <option value="modulo">Módulo</option>
                    <option value="defensa">Defensa</option>
                </select>

                <div id="sub-select-modulo" class="pago-sub-categoria">
                    <label class="pago-sub-label">N° de Módulo <span class="required">*</span></label>
                    <input type="number" id="input-modulo-pago" min="1" max="99" placeholder="Ej: 1" disabled>
                    <p id="modulo-confirmacion" class="pago-modulo-conf"></p>
                </div>

                <div id="sub-select-defensa" class="pago-sub-categoria">
                    <label class="pago-sub-label">Tipo de Defensa <span class="required">*</span></label>
                    <select id="select-defensa-pago" disabled>
                        <option value="">Seleccione un alumno y curso primero...</option>
                    </select>
                </div>
            </div>

        </div>

        {{-- COLUMNA DERECHA --}}
        <div class="pago-col-right">

            {{-- Monto + Fecha --}}
            <div class="pago-card-col">
                <div class="pago-inline-row">
                    <div class="pago-inline-group" style="flex:1.4;">
                        <div class="pago-card-col-label">Monto a Pagar <span class="required">*</span></div>
                        <div class="pago-input-monto-col">
                            <span class="prefix">Bs</span>
                            <input type="number" id="monto" name="monto" step="0.01" min="0.01" placeholder="0.00" required disabled>
                        </div>
                    </div>
                    <div class="pago-inline-group" style="flex:1;">
                        <div class="pago-card-col-label">Fecha de Pago <span class="required">*</span></div>
                        <input type="date" id="fecha_pago" name="fecha_pago" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            {{-- Comprobante + Observaciones --}}
            <div class="pago-card-col">
                <div class="pago-card-col-label">Número de Comprobante / Recibo <span class="required">*</span></div>
                <input type="text" id="nro_comprobante" name="nro_comprobante" placeholder="Ej: 001-98765432" required>

                <div style="margin-top:12px;">
                    <div class="pago-card-col-label">Comprobante Digital (JPG, PNG, PDF)</div>
                    <div class="pago-dropzone-col" id="dropzone-comprobante">
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

                <div style="margin-top:12px;">
                    <div class="pago-card-col-label">Observaciones (Opcional)</div>
                    <textarea id="observacion" name="observacion" placeholder="Agregue cualquier detalle adicional sobre este pago..."></textarea>
                </div>
            </div>

        </div>

    </div>

    {{-- Botones --}}
    <div class="pago-form-footer">
        @if($detalle)
        <a href="{{ route('caja.index', ['estudiante_id' => $estudiante->id, 'inscripcion_id' => $inscripcion->id]) }}" class="btn-cancelar">Cancelar</a>
        @else
        <a href="{{ route('caja.index') }}" class="btn-cancelar">Cancelar</a>
        @endif
        <button type="submit" class="btn-confirmar-pago" id="btn-confirmar-pago" {{ $detalle ? '' : 'disabled' }}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>Confirmar Pago</span>
        </button>
    </div>
</form>

<script src="{{ asset('js/caja_pago.js') }}?v={{ filemtime(public_path('js/caja_pago.js')) }}"></script>

@endsection
