<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Curso;
use App\Models\Documento;
use App\Traits\GeneraPlanDePagos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EstudianteController extends Controller
{
    use GeneraPlanDePagos;

    /**
     * Listado de estudiantes (como en la imagen 134808)
     */
    public function index(Request $request)
    {
        $query = Estudiante::with(['inscripciones.curso', 'documentos']);

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
            'inscribir_ahora' => 'sometimes|accepted',
        ];

        // Si el usuario marcó "inscribir ahora", validar campos de inscripción
        if ($request->filled('inscribir_ahora')) {
            $rules = array_merge($rules, [
                'curso_id' => 'required|exists:cursos,id',
                'fecha_inscripcion' => 'required|date',
                'modalidad_pago' => 'required|in:Contado,Cuotas',
                'descuento_porcentaje' => 'nullable|integer|min:0|max:100',
            ]);
        }

        $messages = [
            'inscribir_ahora.accepted' => 'Debe aceptar la opción de inscribir ahora si desea registrar al estudiante en un curso.',
            'curso_id.required' => 'Debe seleccionar un curso si desea inscribir al estudiante ahora.',
            'fecha_inscripcion.required' => 'Debe indicar la fecha de inscripción.',
            'modalidad_pago.required' => 'Debe elegir una modalidad de pago.',
        ];

        $validated = $request->validate($rules, $messages);

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
                $curso = Curso::find($validated['curso_id']);
                $tipoInscripcion = $curso->tipo;

                $inscripcion = Inscripcion::create([
                    'estudiante_id' => $estudiante->id,
                    'curso_id' => $validated['curso_id'],
                    'tipo_inscripcion' => $tipoInscripcion,
                    'fecha_inscripcion' => $validated['fecha_inscripcion'],
                    'estado_academico' => 'Pendiente',
                    'estado_financiero' => 'Sin Pagar',
                    'modalidad_pago' => $validated['modalidad_pago'],
                    'observacion' => null,
                ]);

                // 3. Crear plan de pagos y detalles por módulos/defensa
                $descuento = $estudiante->descuento_porcentaje / 100;

                $this->crearPlanDePagos(
                    $inscripcion,
                    $curso,
                    $descuento,
                    $validated['modalidad_pago'],
                    $tipoInscripcion
                );
            }
        });

        $msg = !empty($validated['curso_id'])
            ? 'Estudiante inscrito correctamente.'
            : 'Estudiante registrado correctamente.';

        return redirect()->route('estudiantes.index')
            ->with('success', $msg);
    }

    /**
     * Inscribir estudiante en un curso (desde el modal del perfil)
     */
    public function inscribirCurso(Request $request, Estudiante $estudiante)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'fecha_inscripcion' => 'required|date',
            'modalidad_pago' => 'required|in:Contado,Cuotas',
            'porcentaje_descuento' => 'nullable|numeric|min:0|max:100',
        ]);

        $existe = Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('curso_id', $validated['curso_id'])
            ->exists();

        if ($existe) {
            return back()->with('error', 'El estudiante ya está inscrito en este curso.');
        }

        $curso = Curso::find($validated['curso_id']);
        $tipoInscripcion = $curso->tipo;
        $descuento = ($validated['porcentaje_descuento'] ?? 0) / 100;

        DB::transaction(function () use ($estudiante, $validated, $curso, $tipoInscripcion, $descuento) {
            $inscripcion = Inscripcion::create([
                'estudiante_id' => $estudiante->id,
                'curso_id' => $validated['curso_id'],
                'tipo_inscripcion' => $tipoInscripcion,
                'fecha_inscripcion' => $validated['fecha_inscripcion'],
                'estado_academico' => 'Pendiente',
                'estado_financiero' => 'Sin Pagar',
                'modalidad_pago' => $validated['modalidad_pago'],
                'observacion' => null,
            ]);

            $this->crearPlanDePagos(
                $inscripcion,
                $curso,
                $descuento,
                $validated['modalidad_pago'],
                $tipoInscripcion
            );
        });

        return redirect()->back()
            ->with('success', 'Inscripción agregada correctamente.');
    }

    /**
     * Detalle del estudiante (como en la imagen 134919)
     */
    public function show(Request $request, Estudiante $estudiante)
    {
        $estudiante->load(['inscripciones.curso', 'documentos']);
        $cursos = Curso::where('activo', true)->get();

        // Tipos de documentos requeridos
        $tiposDocumentos = ['Grado de Bachiller', 'Certificado de Idiomas', 'Copia de DNI / Pasaporte'];
        $documentosSubidos = $estudiante->documentos->pluck('tipo')->toArray();

        // Breadcrumb contextual según el origen de la navegación.
        // Por defecto vuelve a "Gestión de Alumnos"; si se llega desde el detalle
        // de un programa (from=programa&programa_id=X), vuelve a ese programa.
        $breadcrumb = [
            'url'   => route('estudiantes.index'),
            'label' => 'Gestión de Alumnos',
        ];
        if ($request->query('from') === 'programa' && $request->filled('programa_id')) {
            $programa = Curso::find($request->query('programa_id'));
            if ($programa) {
                $breadcrumb = [
                    'url'   => route('programas.show', $programa->id),
                    'label' => $programa->nombre,
                ];
            }
        }

        return view('estudiantes.show', compact('estudiante', 'cursos', 'tiposDocumentos', 'documentosSubidos', 'breadcrumb'));
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

    /**
     * Dar de baja un estudiante (marcar como inactivo)
     */
    public function destroy(Estudiante $estudiante)
    {
        $estudiante->update(['activo' => false]);

        return redirect()->route('estudiantes.index')
            ->with('success', 'El alumno "' . $estudiante->nombre_completo . '" ha sido dado de baja correctamente.');
    }

    /**
     * Reactivar un estudiante dado de baja
     */
    public function reactivate(Estudiante $estudiante)
    {
        $estudiante->update(['activo' => true]);

        return redirect()->route('estudiantes.index')
            ->with('success', 'El alumno "' . $estudiante->nombre_completo . '" ha sido reactivado correctamente.');
    }
}