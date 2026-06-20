<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    /**
     * Almacenar un documento subido para un estudiante.
     */
    public function store(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'tipo' => 'required|string|max:50',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $file = $request->file('archivo');
        $path = $file->store('documentos/' . $estudiante->id, 'public');

        Documento::create([
            'estudiante_id' => $estudiante->id,
            'tipo' => $request->tipo,
            'nombre_archivo' => $file->getClientOriginalName(),
            'ruta_archivo' => $path,
        ]);

        if ($request->input('redirect_to') === 'index') {
            return redirect()->route('estudiantes.index')
                ->with('success', 'Documento subido correctamente.');
        }

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('success', 'Documento subido correctamente.');
    }

    /**
     * Descargar un documento.
     */
    public function descargar(Documento $documento)
    {
        $fullPath = storage_path('app/public/' . $documento->ruta_archivo);

        if (!file_exists($fullPath)) {
            abort(404, 'Archivo no encontrado.');
        }

        return response()->download($fullPath, $documento->nombre_archivo);
    }

    /**
     * Eliminar un documento.
     */
    public function destroy(Documento $documento)
    {
        $estudianteId = $documento->estudiante_id;

        $fullPath = storage_path('app/public/' . $documento->ruta_archivo);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $documento->delete();

        return redirect()->route('estudiantes.show', $estudianteId)
            ->with('success', 'Documento eliminado correctamente.');
    }
}
