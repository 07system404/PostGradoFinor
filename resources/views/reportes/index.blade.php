@extends('layouts.app')

@section('title', 'Reportes y Planillas')

@section('content')

<link rel="stylesheet" href="{{ asset('css/reportes.css') }}?v={{ filemtime(public_path('css/reportes.css')) }}">

@if(session('error'))
<div class="alert alert-error">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>{{ session('error') }}</span>
</div>
@endif

@if(session('success'))
<div class="alert alert-success">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

<div class="reportes-container">

    {{-- Planilla Excel --}}
    <div class="reporte-card">
        <div class="reporte-card-topbar excel"></div>
        <div class="reporte-card-header excel">
            <div class="reporte-icon excel">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <div>
                <h3>Planilla de Pagos por Alumno</h3>
                <p>Exporte las fechas de pago de cada modulo, matricula y defensas.</p>
            </div>
        </div>
        <div class="reporte-card-body">
            <div class="form-group">
                <label>Programa Academico</label>
                <select id="excel_curso_id" class="form-control" required>
                    <option value="">-- Seleccione un programa --</option>
                    @foreach($cursos as $c)
                    <option value="{{ $c->id }}">{{ $c->tipo }}: {{ $c->nombre }} (V.{{ $c->version }})</option>
                    @endforeach
                </select>
            </div>
            <div class="reporte-info-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Columnas dinamicas de <strong>Fecha y Monto</strong> segun los modulos del programa. Formato nativo .xlsx.</span>
            </div>
            <button type="button" id="btnDescargarExcel" class="btn-reporte btn-excel">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Descargar Planilla (.xlsx)
            </button>
        </div>
    </div>

    {{-- Cuentas por Cobrar --}}
    <div class="reporte-card">
        <div class="reporte-card-topbar excel"></div>
        <div class="reporte-card-header excel">
            <div class="reporte-icon excel">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <h3>Cuentas por Cobrar</h3>
                <p>Resumen financiero por programa: total programado, pagado y saldo pendiente.</p>
            </div>
        </div>
        <div class="reporte-card-body">
            <div class="reporte-info-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Columnas: <strong>N°</strong>, <strong>Nombre del Curso</strong>, <strong>Total Programado + Matrícula</strong>, <strong>Total Pagado</strong>, <strong>Total por Pagar</strong>. Incluye fila de <strong>TOTALES GENERALES</strong>.</span>
            </div>
            <a href="{{ route('reportes.cuentas.cobrar') }}" class="btn-reporte btn-excel" id="btnCuentasCobrar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Exportar Cuentas por Cobrar (.xlsx)
            </a>
        </div>
    </div>

    {{-- Pagos por Rango --}}
    <div class="reporte-card">
        <div class="reporte-card-topbar excel"></div>
        <div class="reporte-card-header excel">
            <div class="reporte-icon excel">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <h3>Pagos por Rango de Fechas</h3>
                <p>Exporte todos los pagos realizados entre dos fechas.</p>
            </div>
        </div>
        <div class="reporte-card-body">
            <form method="GET" action="{{ route('reportes.pagos.rango') }}">
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                    <div class="form-group" style="flex:1;min-width:150px;">
                        <label>Desde</label>
                        <input type="date" name="desde" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;min-width:150px;">
                        <label>Hasta</label>
                        <input type="date" name="hasta" class="form-control" required>
                    </div>
                </div>
                <div class="reporte-info-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>Columnas: Curso, Tipo de Pago (Matricula, Modulo, Defensa), Monto y Fecha.</span>
                </div>
                <button type="submit" id="btnDescargarRango" class="btn-reporte btn-excel" style="background:linear-gradient(135deg, #16a34a, #15803d);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Exportar Pagos (.xlsx)
                </button>
            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('btnDescargarExcel');
    var sel = document.getElementById('excel_curso_id');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var id = sel.value;
        if (!id) { sel.focus(); return; }
        btn.disabled = true;
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Procesando...';
        window.location.href = '{{ url("reportes/exportar-planilla") }}/' + id;
        setTimeout(function() {
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Descargar Planilla (.xlsx)';
        }, 3000);
    });
});
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

@endsection
