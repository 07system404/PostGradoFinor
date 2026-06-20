<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\Estudiante;
use Illuminate\Http\Request;

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

        return redirect()->route('estudiantes.show', $estudiante)
            ->with('success', 'Documento subido correctamente.');
    }

}
