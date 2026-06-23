<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; padding: 30px; }
        
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1B4FD8; padding-bottom: 15px; }
        .header h1 { font-size: 20px; color: #1B4FD8; margin-bottom: 5px; }
        .header .subtitle { font-size: 13px; color: #666; }
        
        .info-grid { display: flex; gap: 20px; margin-bottom: 25px; }
        .info-box { flex: 1; background: #f8f9fa; border-radius: 8px; padding: 12px 15px; border-left: 4px solid #1B4FD8; }
        .info-box .label { font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-box .value { font-size: 13px; font-weight: 700; color: #333; }
        
        .resumen-grid { display: flex; gap: 15px; margin-bottom: 25px; }
        .resumen-item { flex: 1; text-align: center; padding: 10px; border-radius: 8px; }
        .resumen-item.programado { background: #e8f0fe; }
        .resumen-item.pagado { background: #e6f4ea; }
        .resumen-item.pendiente { background: #fce8e6; }
        .resumen-item .res-label { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 4px; }
        .resumen-item .res-monto { font-size: 16px; font-weight: 800; }
        .resumen-item.programado .res-monto { color: #1B4FD8; }
        .resumen-item.pagado .res-monto { color: #2E7D32; }
        .resumen-item.pendiente .res-monto { color: #D32F2F; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead th { background: #1B4FD8; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        
        .estado-pagado { color: #2E7D32; font-weight: 700; }
        .estado-vencido { color: #D32F2F; font-weight: 700; }
        .estado-pendiente { color: #F57C00; font-weight: 700; }
        .estado-programado { color: #666; font-weight: 600; }
        
        .footer { text-align: center; margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cronograma de Pagos</h1>
        <div class="subtitle">PostGrado — Sistema de Gestión Académica</div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="label">Estudiante</div>
            <div class="value">{{ $estudiante->nombre_completo }}</div>
        </div>
        <div class="info-box">
            <div class="label">Cédula</div>
            <div class="value">{{ $estudiante->cedula ?? '—' }}</div>
        </div>
        <div class="info-box">
            <div class="label">Programa</div>
            <div class="value">{{ $inscripcion->curso->nombre }}</div>
        </div>
        <div class="info-box">
            <div class="label">Modalidad</div>
            <div class="value">{{ $inscripcion->tipo_inscripcion }}</div>
        </div>
    </div>

    <div class="resumen-grid">
        <div class="resumen-item programado">
            <div class="res-label">Monto Total Programa</div>
            <div class="res-monto">Bs {{ number_format($planPago->monto_total_programado, 2) }}</div>
        </div>
        <div class="resumen-item pagado">
            <div class="res-label">Total Pagado</div>
            <div class="res-monto">Bs {{ number_format($planPago->monto_total_pagado, 2) }}</div>
        </div>
        <div class="resumen-item pendiente">
            <div class="res-label">Saldo Pendiente</div>
            <div class="res-monto">Bs {{ number_format($planPago->saldo_pendiente, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Concepto / Módulo</th>
                <th>F. Vencimiento</th>
                <th>Monto</th>
                <th>Pagado</th>
                <th>Saldo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
                @php
                    $saldoCuota = $detalle->monto_programado - $detalle->monto_pagado;
                    $isVencido = ($detalle->estado === 'Vencido') ||
                        ($detalle->estado === 'Pendiente' && $detalle->fecha_vencimiento && $detalle->fecha_vencimiento < now());

                    $claseEstado = match($detalle->estado) {
                        'Pagado' => 'estado-pagado',
                        'Vencido' => 'estado-vencido',
                        'Pendiente' => $isVencido ? 'estado-vencido' : 'estado-pendiente',
                        'Parcial' => 'estado-pendiente',
                        default => 'estado-programado',
                    };

                    $textoEstado = match($detalle->estado) {
                        'Pagado' => 'Pagado',
                        'Vencido' => 'Vencido',
                        'Pendiente' => $isVencido ? 'Vencido' : 'Pendiente',
                        'Parcial' => 'Parcial',
                        default => 'Programado',
                    };
                @endphp
                <tr>
                    <td>{{ $detalle->concepto }}</td>
                    <td>{{ $detalle->fecha_vencimiento ? $detalle->fecha_vencimiento->format('d/m/Y') : '—' }}</td>
                    <td>Bs {{ number_format($detalle->monto_programado, 2) }}</td>
                    <td>Bs {{ number_format($detalle->monto_pagado, 2) }}</td>
                    <td>Bs {{ number_format($saldoCuota, 2) }}</td>
                    <td class="{{ $claseEstado }}">{{ $textoEstado }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y \a\s H:i') }} — PostGrado Sistema de Gestión Académica
    </div>
</body>
</html>
