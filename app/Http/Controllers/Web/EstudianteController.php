<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Curso;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EstudianteController extends Controller
{
    /**
     * Listado de estudiantes (como en la imagen 134808)
     */
    public function index(Request $request)
    {
        $query = Estudiante::with(['inscripciones.curso', 'documentos'])
            ->where('activo', true);

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'LIKE', "%$search%")
                    ->orWhere('paterno', 'LIKE', "%$search%")
                    ->orWhere('materno', 'LIKE', "%$search%")
                    ->orWhere('registro', 'LIKE', "%$search%")
                    ->orWhere('cedula', 'LIKE', "%$search%");
            });
        }

        // Filtro por curso (subconsulta)
        if ($request->filled('curso_id')) {
            $query->whereHas('inscripciones', function ($q) use ($request) {
                $q->where('curso_id', $request->curso_id);
            });
        }

        $estudiantes = $query->orderBy('created_at', 'desc')->paginate(10);
        $cursos = Curso::where('activo', true)->get();

        // Estadísticas
        $totalAlumnos = $estudiantes->total();
        $solventes = Inscripcion::whereHas('estudiante', function($q) {
                $q->where('activo', true);
            })
            ->whereIn('estado_financiero', ['Al Día', 'Completado'])
            ->distinct('estudiante_id')
            ->count('estudiante_id');

        return view('estudiantes.index', compact('estudiantes', 'cursos', 'totalAlumnos', 'solventes'));
    }

    /**
     * Formulario de inscripción rápida (como en la imagen 134958)
     */
    public function create()
    {
        $cursos = Curso::where('activo', true)->get();
        return view('estudiantes.create', compact('cursos'));
    }

    /**
     * Guardar nuevo estudiante (inscripción opcional)
     */
    public function store(Request $request)
    {
        $rules = [
            'nombres' => 'required|string|max:50',
            'paterno' => 'required|string|max:20',
            'materno' => 'nullable|string|max:20',
            'cedula' => 'required|string|max:20|unique:estudiantes,cedula',
            'celular' => 'nullable|string|max:20',
            'registro' => 'required|string|max:20|unique:estudiantes,registro',
            'observaciones' => 'nullable|string|max:255',
            'inscribir_ahora' => 'nullable|in:1',
        ];

        // Si el usuario marcó "inscribir ahora", validar campos de inscripción
        if ($request->filled('inscribir_ahora')) {
            $rules = array_merge($rules, [
                'curso_id' => 'required|exists:cursos,id',
                'tipo_inscripcion' => 'required|in:Diplomado,Especialidad,Maestría',
                'fecha_inscripcion' => 'required|date',
                'modalidad_pago' => 'required|in:Contado,Cuotas',
                'descuento_porcentaje' => 'nullable|integer|min:0|max:100',
            ]);
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated) {
            // 1. Crear estudiante (siempre)
            $estudiante = Estudiante::create([
                'nombres' => $validated['nombres'],
                'paterno' => $validated['paterno'],
                'materno' => $validated['materno'] ?? null,
                'cedula' => $validated['cedula'],
                'celular' => $validated['celular'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'descuento_porcentaje' => $validated['descuento_porcentaje'] ?? 0,
                'registro' => $validated['registro'],
                'activo' => true,
            ]);

            // 2. Solo crear inscripción si el usuario marcó la opción
            if (!empty($validated['curso_id'])) {
                $inscripcion = Inscripcion::create([
                    'estudiante_id' => $estudiante->id,
                    'curso_id' => $validated['curso_id'],
                    'tipo_inscripcion' => $validated['tipo_inscripcion'] ?? 'Diplomado',
                    'fecha_inscripcion' => $validated['fecha_inscripcion'],
                    'estado_academico' => 'Pendiente',
                    'estado_financiero' => 'Sin Pagar',
                    'modalidad_pago' => $validated['modalidad_pago'],
                    'observacion' => null,
                ]);

                // 3. Crear plan de pagos
                $curso = Curso::find($validated['curso_id']);
                $total = $curso->costo_total_estudio;
                $descuento = $estudiante->descuento_porcentaje / 100;
                $totalConDescuento = $total - ($total * $descuento);

                $plan = \App\Models\PlanPago::create([
                    'inscripcion_id' => $inscripcion->id,
                    'monto_total_programado' => $totalConDescuento,
                    'monto_total_pagado' => 0,
                    'saldo_pendiente' => $totalConDescuento,
                    'total_cuotas' => $validated['modalidad_pago'] === 'Contado' ? 1 : 6,
                    'estado' => 'Pendiente',
                ]);

                // Crear cuotas
                $cuotas = $validated['modalidad_pago'] === 'Contado' ? 1 : 6;
                $montoCuota = $totalConDescuento / $cuotas;
                for ($i = 1; $i <= $cuotas; $i++) {
                    \App\Models\DetallePlanPago::create([
                        'plan_pago_id' => $plan->id,
                        'nro_cuota' => $i,
                        'nro_modulo' => null,
                        'concepto' => 'Cuota '.$i,
                        'fase' => null,
                        'monto_programado' => $montoCuota,
                        'monto_pagado' => 0,
                        'monto_descuento' => 0,
                        'saldo_cuota' => $montoCuota,
                        'fecha_vencimiento' => now()->addMonths($i),
                        'estado' => 'Pendiente',
                    ]);
                }
            }
        });

        $msg = !empty($validated['curso_id'])
            ? 'Estudiante inscrito correctamente.'
            : 'Estudiante registrado correctamente.';

        return redirect()->route('estudiantes.index')
            ->with('success', $msg);
    }

    /**
     * Detalle del estudiante (como en la imagen 134919)
     */
    public function show(Estudiante $estudiante)
    {
        $estudiante->load(['inscripciones.curso', 'documentos']);
        $cursos = Curso::where('activo', true)->get();
        
        // Tipos de documentos requeridos
        $tiposDocumentos = ['Grado de Bachiller', 'Certificado de Idiomas', 'Copia de DNI / Pasaporte'];
        $documentosSubidos = $estudiante->documentos->pluck('tipo')->toArray();

        return view('estudiantes.show', compact('estudiante', 'cursos', 'tiposDocumentos', 'documentosSubidos'));
    }

    /**
     * Actualizar datos personales
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:50',
            'paterno' => 'required|string|max:20',
            'materno' => 'nullable|string|max:20',
            'cedula' => 'required|string|max:20|unique:estudiantes,cedula,'.$estudiante->id,
            'celular' => 'nullable|string|max:20',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $estudiante->update($validated);

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('success', 'Datos actualizados correctamente.');
    }

    /**
     * Redirección para edición (no usamos edit separado)
     */
    public function edit(Estudiante $estudiante)
    {
        return redirect()->route('estudiantes.show', $estudiante);
    }
}