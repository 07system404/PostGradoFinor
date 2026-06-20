<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Inscripcion;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    /**
     * Listado de Programas Académicos
     */
    public function index(Request $request)
    {
        $query = Curso::withCount('inscripciones');

        // Búsqueda
        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('tipo', 'like', "%{$buscar}%");
            });
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'activo') {
                $query->where('activo', true);
            } elseif ($estado === 'inactivo') {
                $query->where('activo', false);
            }
        }

        $programas = $query->latest()->paginate(25);

        // Estadísticas
        $totalCupos = $programas->sum('cupo');
        $cuposOcupados = Inscripcion::whereIn('curso_id', $programas->pluck('id'))
            ->whereIn('estado_academico', ['Activo', 'Pendiente'])
            ->count();
        $preInscritos = Inscripcion::where('estado_academico', 'Pendiente')
            ->whereIn('curso_id', $programas->pluck('id'))
            ->count();

        $efectividad = $totalCupos > 0 ? round(($cuposOcupados / $totalCupos) * 100, 0) : 0;

        return view('cursos.index', compact(
            'programas',
            'totalCupos',
            'cuposOcupados',
            'preInscritos',
            'efectividad'
        ));
    }

    /**
     * Formulario de nuevo programa
     */
    public function create()
    {
        return view('cursos.create');
    }

    /**
     * Guardar nuevo programa
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:Diplomado,Especialidad,Maestría',
            'version' => 'required|integer|min:1',
            'edicion' => 'required|integer|min:1',
            'periodo' => 'required|string|max:20',
            'cupo' => 'required|integer|min:1',
            'costo_matricula' => 'required|numeric|min:0',
            'costo_total_estudio' => 'required|numeric|min:0',
            'costo_defensa_diplomado' => 'nullable|numeric|min:0',
            'costo_defensa_especialidad' => 'nullable|numeric|min:0',
            'costo_defensa_maestria' => 'nullable|numeric|min:0',
            'nro_modulos_diplomado' => 'required|integer|min:1',
            'nro_modulos_especialidad' => 'nullable|integer|min:0',
            'nro_modulos_maestria' => 'nullable|integer|min:0',
        ]);

        Curso::create([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'version' => $request->version,
            'edicion' => $request->edicion,
            'periodo' => $request->periodo,
            'cupo' => $request->cupo,
            'costo_matricula' => $request->costo_matricula,
            'costo_total_estudio' => $request->costo_total_estudio,
            'costo_defensa_diplomado' => $request->costo_defensa_diplomado ?? 0,
            'costo_defensa_especialidad' => $request->costo_defensa_especialidad ?? 0,
            'costo_defensa_maestria' => $request->costo_defensa_maestria ?? 0,
            'nro_modulos_diplomado' => $request->nro_modulos_diplomado,
            'nro_modulos_especialidad' => $request->nro_modulos_especialidad,
            'nro_modulos_maestria' => $request->nro_modulos_maestria,
            'activo' => true,
        ]);

        return redirect()->route('programas.index')
            ->with('success', 'Programa académico creado exitosamente.');
    }

    /**
     * Ver detalle del programa
     */
    public function show($id)
    {
        $programa = Curso::with(['inscripciones.estudiante'])->findOrFail($id);
        return view('cursos.show', compact('programa'));
    }

    /**
     * Formulario de edición del programa
     */
    public function edit($id)
    {
        $programa = Curso::findOrFail($id);
        return view('cursos.edit', compact('programa'));
    }

    /**
     * Actualizar programa
     */
    public function update(Request $request, $id)
    {
        $programa = Curso::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:Diplomado,Especialidad,Maestría',
            'version' => 'required|integer|min:1',
            'edicion' => 'required|integer|min:1',
            'periodo' => 'required|string|max:20',
            'cupo' => 'required|integer|min:1',
            'costo_matricula' => 'required|numeric|min:0',
            'costo_total_estudio' => 'required|numeric|min:0',
            'costo_defensa_diplomado' => 'nullable|numeric|min:0',
            'costo_defensa_especialidad' => 'nullable|numeric|min:0',
            'costo_defensa_maestria' => 'nullable|numeric|min:0',
            'nro_modulos_diplomado' => 'required|integer|min:1',
            'nro_modulos_especialidad' => 'nullable|integer|min:0',
            'nro_modulos_maestria' => 'nullable|integer|min:0',
            'activo' => 'boolean',
        ]);

        $programa->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'version' => $request->version,
            'edicion' => $request->edicion,
            'periodo' => $request->periodo,
            'cupo' => $request->cupo,
            'costo_matricula' => $request->costo_matricula,
            'costo_total_estudio' => $request->costo_total_estudio,
            'costo_defensa_diplomado' => $request->costo_defensa_diplomado ?? 0,
            'costo_defensa_especialidad' => $request->costo_defensa_especialidad ?? 0,
            'costo_defensa_maestria' => $request->costo_defensa_maestria ?? 0,
            'nro_modulos_diplomado' => $request->nro_modulos_diplomado,
            'nro_modulos_especialidad' => $request->nro_modulos_especialidad,
            'nro_modulos_maestria' => $request->nro_modulos_maestria,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()->route('programas.index')
            ->with('success', 'Programa actualizado correctamente.');
    }

    /**
     * Eliminar programa
     */
    public function destroy($id)
    {
        $programa = Curso::findOrFail($id);

        // Verificar si tiene inscripciones
        if ($programa->inscripciones()->count() > 0) {
            return redirect()->route('programas.index')
                ->with('error', 'No se puede eliminar el programa porque tiene inscripciones activas.');
        }

        $programa->delete();
        return redirect()->route('programas.index')
            ->with('success', 'Programa eliminado correctamente.');
    }

    /**
     * Activar o desactivar programa según estado actual
     */
    public function toggleActivo($id)
    {
        $programa = Curso::findOrFail($id);
        $nuevoEstado = !$programa->activo;
        $programa->update(['activo' => $nuevoEstado]);

        $mensaje = $nuevoEstado
            ? "Programa activado correctamente."
            : "Programa desactivado correctamente.";

        return redirect()->route('programas.index')->with('success', $mensaje);
    }

    /**
     * Búsqueda en tiempo real (AJAX)
     */
    public function buscarAjax(Request $request)
    {
        $query = Curso::withCount('inscripciones');

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('tipo', 'like', "%{$buscar}%");
            });
        }

        if ($request->filled('estado')) {
            $estado = $request->input('estado');
            if ($estado === 'activo') {
                $query->where('activo', true);
            } elseif ($estado === 'inactivo') {
                $query->where('activo', false);
            }
        }

        $programas = $query->latest()->paginate(25);

        if ($request->ajax()) {
            $sections = view('cursos.index', compact('programas'))->renderSections();
            return response()->json(['html' => $sections['content']]);
        }

        return redirect()->route('programas.index');
    }

    /**
     * Exportar programas
     */
    public function exportar()
    {
        $programas = Curso::withCount('inscripciones')->get();

        $filename = 'programas_academicos_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($programas) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Código', 'Nombre', 'Tipo', 'Versión', 'Edición', 'Periodo', 'Cupo', 'Inscritos', 'Costo Total', 'Estado']);

            foreach ($programas as $prog) {
                $codigo = strtoupper(substr($prog->tipo, 0, 3)) . '-' . 
                          strtoupper(str_replace(' ', '-', substr($prog->nombre, 0, 3))) . '-' .
                          $prog->periodo;
                fputcsv($file, [
                    $codigo,
                    $prog->nombre,
                    $prog->tipo,
                    $prog->version,
                    $prog->edicion,
                    $prog->periodo,
                    $prog->cupo,
                    $prog->inscripciones_count,
                    $prog->costo_total_estudio,
                    $prog->activo ? 'Activo' : 'Inactivo',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
