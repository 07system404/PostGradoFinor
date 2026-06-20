<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Pago;
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

        $headers = ['N°', 'APELLIDOS Y NOMBRES', 'OBSERVACIONES', 'N° REGISTRO', 'CARNET DE IDENTIDAD', 'N° DE CELULAR', 'FECHA MATRÍCULA', 'MATRÍCULA'];

        foreach ($phases as $phase) {
            for ($i = 1; $i <= $nroModulos[$phase]; $i++) {
                $headers[] = "MÓDULO $i ($phase)";
            }
            $defensaLabel = match ($phase) {
                'Diplomado' => 'DEFENSA DIPLOMADO',
                'Especialidad' => 'DEFENSA ESPECIALIDAD',
                'Maestría' => 'DEFENSA MAESTRÍA',
            };
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

        $sheet->fromArray($headers, null, 'A1');
        $sheet->getRowDimension(1)->setRowHeight(30);

        $colCount = count($headers);
        for ($i = 0; $i < $colCount; $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount) . '1')->applyFromArray($headerStyle);

        $row = 2;
        $num = 1;

        foreach ($curso->inscripciones as $inscripcion) {
            $estudiante = $inscripcion->estudiante;
            if (!$estudiante) continue;

            $pagos = [];
            if ($inscripcion->planPago) {
                foreach ($inscripcion->planPago->detalles as $detalle) {
                    $primerPago = $detalle->pagos->sortBy('fecha_pago')->first();
                    $fechaPagado = $primerPago ? $primerPago->fecha_pago->format('d/m/Y') : '';

                    if ($detalle->concepto === 'Matrícula') {
                        $pagos['matricula'] = $fechaPagado;
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
                            $pagos["mod_{$fase}_{$localNum}"] = $fechaPagado;
                        }
                    } elseif (in_array($detalle->concepto, ['Defensa Diplomado', 'Defensa Especialidad', 'Defensa Maestría'])) {
                        $pagos[$detalle->concepto] = $fechaPagado;
                    }
                }
            }

            $rowData = [
                $num,
                $estudiante->nombre_completo,
                $estudiante->observaciones ?? '',
                $estudiante->registro,
                $estudiante->cedula,
                $estudiante->celular ?? '',
                $inscripcion->fecha_inscripcion ? $inscripcion->fecha_inscripcion->format('d/m/Y') : '',
                $pagos['matricula'] ?? '',
            ];

            foreach ($phases as $phase) {
                for ($i = 1; $i <= $nroModulos[$phase]; $i++) {
                    $rowData[] = $pagos["mod_{$phase}_{$i}"] ?? '';
                }
                $defensaName = match ($phase) {
                    'Diplomado' => 'Defensa Diplomado',
                    'Especialidad' => 'Defensa Especialidad',
                    'Maestría' => 'Defensa Maestría',
                };
                $rowData[] = $pagos[$defensaName] ?? '';
            }

            $sheet->fromArray($rowData, null, "A$row");
            $row++;
            $num++;
        }

        $lastRow = $row - 1;
        if ($lastRow >= 2) {
            $sheet->getStyle('A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount) . $lastRow)
                ->applyFromArray([
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

            for ($i = 3; $i <= $colCount; $i++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$lastRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getStyle("B2:B{$lastRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
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

    public function exportarPagosRango(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = $request->desde;
        $hasta = $request->hasta;

        $pagos = Pago::whereBetween('fecha_pago', [$desde, $hasta])
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
