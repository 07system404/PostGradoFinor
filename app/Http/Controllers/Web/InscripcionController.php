<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Inscripcion;
use App\Traits\GeneraPlanDePagos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    use GeneraPlanDePagos;

    /**
     * Mostrar formulario para inscribir a un estudiante en un nuevo curso (modal).
     */
    public function create(Estudiante $estudiante)
    {
        $cursos = Curso::where('activo', true)->get();
        return view('estudiantes.modal_inscripcion', compact('estudiante', 'cursos'));
    }

    /**
     * Guardar nueva inscripción para un estudiante existente.
     */
    public function store(Request $request, Estudiante $estudiante)
    {
        $validated = $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'fecha_inscripcion' => 'required|date',
            'modalidad_pago' => 'required|in:Contado,Cuotas',
            'porcentaje_descuento' => 'nullable|numeric|min:0|max:100',
        ]);

        // Verificar que no esté ya inscrito en ese curso
        $existe = Inscripcion::where('estudiante_id', $estudiante->id)
            ->where('curso_id', $validated['curso_id'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['curso_id' => 'El estudiante ya está inscrito en este curso.']);
        }

        $curso = Curso::find($validated['curso_id']);
        $tipoInscripcion = $curso->tipo;

        DB::transaction(function () use ($estudiante, $validated, $curso, $tipoInscripcion) {
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

            $descuento = ($validated['porcentaje_descuento'] ?? $estudiante->descuento_porcentaje) / 100;

            $this->crearPlanDePagos(
                $inscripcion,
                $curso,
                $descuento,
                $validated['modalidad_pago'],
                $tipoInscripcion
            );
        });

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('success', 'Inscripción agregada correctamente.');
    }
}