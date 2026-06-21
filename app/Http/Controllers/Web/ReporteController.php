<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteController extends Controller
{
    public function index()
    {
        $cursos = Curso::where('activo', true)->orderBy('nombre')->get();
        return view('reportes.index', compact('cursos'));
    }

    public function exportarCursos(Curso $curso)
    {
        $curso->load(['inscripciones.estudiante', 'inscripciones.planPago.detalles.pagos']);

        $tipo = $curso->tipo;

        $phases = ['Diplomado'];
        if (in_array($tipo, ['Especialidad', 'Maestría'])) {
            $phases[] = 'Especialidad';
        }
        if ($tipo === 'Maestría') {
            $phases[] = 'Maestría';
        }

        $nroModulos = [
            'Diplomado' => (int)($curso->nro_modulos_diplomado ?? 0),
            'Especialidad' => (int)($curso->nro_modulos_especialidad ?? 0),
            'Maestría' => (int)($curso->nro_modulos_maestria ?? 0),
        ];

        //  ENCABEZADOS: 2 columnas por concepto (FECHA + MONTO)
        $headers = ['N°', 'APELLIDOS Y NOMBRES', 'OBSERVACIONES', 'N° REGISTRO', 'CARNET DE IDENTIDAD', 'N° DE CELULAR', 'FECHA MATRÍCULA', 'MATRÍCULA'];

        foreach ($phases as $phase) {
            for ($i = 1; $i <= $nroModulos[$phase]; $i++) {
                $headers[] = "FECHA MÓDULO $i ($phase)";
                $headers[] = "MÓDULO $i ($phase)";
            }
            $defensaLabel = match ($phase) {
                'Diplomado' => 'DEFENSA DIPLOMADO',
                'Especialidad' => 'DEFENSA ESPECIALIDAD',
                'Maestría' => 'DEFENSA MAESTRÍA',
            };
            $headers[] = "FECHA $defensaLabel";
            $headers[] = $defensaLabel;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Pagos');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4FD8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $colCount = count($headers);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // ── FILA 1: TÍTULO DEL CURSO (combinada) ──
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', strtoupper($curso->tipo) . ' EN ' . mb_strtoupper($curso->nombre));
        $sheet->getRowDimension(1)->setRowHeight(36);
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4FD8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A1')->applyFromArray($titleStyle);

        // ── FILA 2: VERSIÓN · EDICIÓN · GESTIÓN (combinada) ──
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', "Versión: {$curso->version}  ·  Edición: {$curso->edicion}  ·  Gestión: {$curso->periodo}");
        $sheet->getRowDimension(2)->setRowHeight(24);
        $subtitleStyle = [
            'font' => ['italic' => true, 'size' => 11, 'color' => ['rgb' => '2C3E50']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A2')->applyFromArray($subtitleStyle);

        // ── FILA 3: ENCABEZADOS ──
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getRowDimension(3)->setRowHeight(30);

        for ($i = 0; $i < $colCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet->getStyle('A3:' . $lastColLetter . '3')->applyFromArray($headerStyle);

        // ── FILAS DE DATOS ──
        $row = 4;
        $num = 1;

        foreach ($curso->inscripciones as $inscripcion) {
            $estudiante = $inscripcion->estudiante;
            if (!$estudiante) continue;

            // Construir $pagos: cada concepto guarda ['fecha' => ..., 'monto' => ...]
            $pagos = [];
            if ($inscripcion->planPago) {
                foreach ($inscripcion->planPago->detalles as $detalle) {
                    // Condonado → mostrar vacío (no es deuda pendiente)
                    if ($detalle->estado === 'Condonado') {
                        $info = ['fecha' => '', 'monto' => ''];
                    } else {
                        $primerPago = $detalle->pagos->sortBy('fecha_pago')->first();
                        $fechaPagado = $primerPago ? $primerPago->fecha_pago->format('d/m/Y') : '';
                        $montoPagado = $primerPago ? $detalle->monto_pagado : 0;
                        $info = ['fecha' => $fechaPagado, 'monto' => $montoPagado ?? 0];
                    }

                    if ($detalle->concepto === 'Matrícula') {
                        $pagos['matricula'] = $info;
                    } elseif (preg_match('/Módulo (\d+)/i', $detalle->concepto, $m)) {
                        $globalNum = (int)$m[1];
                        $fase = $detalle->fase;
                        if (!in_array($fase, $phases)) continue;

                        $localNum = $globalNum;
                        if ($fase === 'Especialidad') {
                            $localNum -= $nroModulos['Diplomado'];
                        } elseif ($fase === 'Maestría') {
                            $localNum -= $nroModulos['Diplomado'] + $nroModulos['Especialidad'];
                        }

                        if ($localNum >= 1 && $localNum <= $nroModulos[$fase]) {
                            $pagos["mod_{$fase}_{$localNum}"] = $info;
                        }
                    } elseif (in_array($detalle->concepto, ['Defensa Diplomado', 'Defensa Especialidad', 'Defensa Maestría'])) {
                        $pagos[$detalle->concepto] = $info;
                    }
                }
            }

            // Armar fila: primero los datos fijos + matrícula (fecha + monto)
            // OBSERVACIONES: usar $inscripcion->observacion (contiene historial DIO BAJA, SOLO DIPLOMADO, etc.)
            $observacion = $inscripcion->observacion ?? ($estudiante->observaciones ?? '');
            $rowData = [
                $num,
                $estudiante->nombre_completo,
                $observacion,
                $estudiante->registro,
                $estudiante->cedula,
                $estudiante->celular ?? '',
                $inscripcion->fecha_inscripcion ? $inscripcion->fecha_inscripcion->format('d/m/Y') : '',
                $pagos['matricula']['monto'] ?? 0,
            ];

            // Luego módulos y defensas: SIEMPRE 2 valores por concepto (fecha + monto)
            foreach ($phases as $phase) {
                for ($i = 1; $i <= $nroModulos[$phase]; $i++) {
                    $rowData[] = $pagos["mod_{$phase}_{$i}"]['fecha'] ?? '';
                    $rowData[] = $pagos["mod_{$phase}_{$i}"]['monto'] ?? 0;
                }
                $defensaName = match ($phase) {
                    'Diplomado' => 'Defensa Diplomado',
                    'Especialidad' => 'Defensa Especialidad',
                    'Maestría' => 'Defensa Maestría',
                };
                $rowData[] = $pagos[$defensaName]['fecha'] ?? '';
                $rowData[] = $pagos[$defensaName]['monto'] ?? 0;
            }

            $sheet->fromArray($rowData, null, "A$row");
            $row++;
            $num++;
        }

        // ── ESTILOS DE DATOS ──
        $lastRow = $row - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle('A4:' . $lastColLetter . $lastRow)
                ->applyFromArray([
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

            // Centrar todas las columnas excepto APELLIDOS Y NOMBRES (B)
            for ($i = 1; $i <= $colCount; $i++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                if ($i === 2) {
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                } else {
                    $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }

            // Formato de moneda para columnas pares (monto) desde la columna H (8) en adelante
            // Las columnas de monto son las pares dentro de cada par (fecha, monto): H, J, L, ...
            for ($i = 8; $i <= $colCount; $i += 2) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getStyle("{$colLetter}4:{$colLetter}{$lastRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $curso->nombre);
        $filename = "reporte_pagos_{$safeName}_" . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function cuentasPorCobrar()
    {
        $cursos = Curso::with(['inscripciones.planPago'])
            ->where('activo', true)
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cuentas por Cobrar');

        $headers = ['N°', 'NOMBRE DEL CURSO', 'TOTAL PROGRAMADO + MATRÍCULA', 'TOTAL PAGADO', 'TOTAL POR PAGAR'];

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4FD8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getRowDimension(1)->setRowHeight(30);

        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        $row = 2;
        $num = 1;
        $totalProgramado = 0;
        $totalPagado = 0;
        $totalPorPagar = 0;

        foreach ($cursos as $curso) {
            $cursoProgramado = 0;
            $cursoPagado = 0;

            foreach ($curso->inscripciones as $inscripcion) {
                if ($inscripcion->planPago) {
                    $cursoProgramado += $inscripcion->planPago->monto_total_programado ?? 0;
                    $cursoPagado += $inscripcion->planPago->monto_total_pagado ?? 0;
                }
            }

            $cursoPorPagar = $cursoProgramado - $cursoPagado;

            $sheet->setCellValue("A$row", $num);
            $sheet->setCellValue("B$row", "{$curso->tipo}: {$curso->nombre} (V.{$curso->version})");
            $sheet->setCellValue("C$row", $cursoProgramado);
            $sheet->setCellValue("D$row", $cursoPagado);
            $sheet->setCellValue("E$row", $cursoPorPagar);

            $totalProgramado += $cursoProgramado;
            $totalPagado += $cursoPagado;
            $totalPorPagar += $cursoPorPagar;

            $row++;
            $num++;
        }

        // Fila de TOTALES GENERALES — respeta las 5 columnas
        $totalsRow = $row;
        $sheet->setCellValue("A$totalsRow", '');
        $sheet->setCellValue("B$totalsRow", 'TOTALES GENERALES');
        $sheet->setCellValue("C$totalsRow", $totalProgramado);
        $sheet->setCellValue("D$totalsRow", $totalPagado);
        $sheet->setCellValue("E$totalsRow", $totalPorPagar);

        // Estilo para la fila de totales
        $totalsStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle("A{$totalsRow}:E{$totalsRow}")->applyFromArray($totalsStyle);
        $sheet->getStyle("C{$totalsRow}:E{$totalsRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Estilo para filas de datos
        if ($totalsRow > 2) {
            $dataLastRow = $totalsRow - 1;
            $sheet->getStyle("A2:E{$dataLastRow}")
                ->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
            $sheet->getStyle("C2:E{$dataLastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A2:A{$dataLastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$dataLastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $filename = 'cuentas_por_cobrar_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportarPagosRango(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        // Convertir a Carbon para manejo correcto de fechas (Corrección del bug)
        $desde = Carbon::parse($request->desde)->startOfDay();
        $hasta = Carbon::parse($request->hasta)->endOfDay();

        // NOTA: Se usa whereDate() porque SQLite almacena fecha_pago con componente
        // de hora ('Y-m-d H:i:s') aunque la migración defina date ('Y-m-d').
        // whereBetween() con strings falla porque la comparación binaria de SQLite
        // no iguala '2026-06-20' con '2026-06-20 00:00:00'.
        $pagos = Pago::whereDate('fecha_pago', '>=', $desde)
            ->whereDate('fecha_pago', '<=', $hasta)
            ->with('inscripcion.estudiante', 'inscripcion.curso', 'detallePlanPago')
            ->orderBy('fecha_pago')
            ->orderBy('inscripcion_id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pagos por Rango');

        $headers = ['ESTUDIANTE', 'CÉDULA', 'CURSO', 'TIPO DE PAGO', 'MONTO', 'FECHA'];

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B4FD8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getRowDimension(1)->setRowHeight(28);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($pagos as $pago) {
            $estudiante = $pago->inscripcion?->estudiante;
            $curso = $pago->inscripcion?->curso;
            $concepto = $pago->detallePlanPago?->concepto ?? '—';

            $sheet->setCellValue("A$row", $estudiante?->nombre_completo ?? '—');
            $sheet->setCellValue("B$row", $estudiante?->cedula ?? '—');
            $sheet->setCellValue("C$row", $curso?->nombre ?? '—');
            $sheet->setCellValue("D$row", $concepto);
            $sheet->setCellValue("E$row", $pago->monto);
            $sheet->setCellValue("F$row", $pago->fecha_pago->format('d/m/Y'));

            $row++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle("A2:F{$lastRow}")
                ->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
            $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $filename = "pagos_rango_{$desde}_a_{$hasta}_" . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
