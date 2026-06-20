<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BackupController extends Controller
{
    private string $backupDir = 'backups';

    // ─── PANTALLA PRINCIPAL ───────────────────────────────────────────────────
    public function index()
    {
        $dbPath   = database_path('database.sqlite');
        $tamanoDb = file_exists($dbPath) ? $this->formatBytes(filesize($dbPath)) : '—';

        $ultimoRespaldo = 'Nunca';
        $historial      = [];

        $backupPath = storage_path('app/' . $this->backupDir);
        if (is_dir($backupPath)) {
            $archivos = glob($backupPath . '/respaldo_*.sqlite');
            usort($archivos, fn($a, $b) => filemtime($b) - filemtime($a));

            foreach ($archivos as $archivo) {
                $nombre      = basename($archivo);
                $fecha       = Carbon::createFromTimestamp(filemtime($archivo))->format('d/m/Y H:i');
                $tamaño      = $this->formatBytes(filesize($archivo));
                $historial[] = compact('nombre', 'fecha', 'tamaño');
            }

            if (!empty($historial)) {
                $ultimoRespaldo = $historial[0]['fecha'];
            }
        }

        return view('backup.index', compact('tamanoDb', 'ultimoRespaldo', 'historial'));
    }

    // ─── GENERAR Y DESCARGAR RESPALDO ────────────────────────────────────────
    public function generate()
    {
        $dbPath = database_path('database.sqlite');

        if (!file_exists($dbPath)) {
            return back()->with('error', 'No se encontró el archivo de base de datos.');
        }

        $nombreArchivo = 'respaldo_' . now()->format('Y-m-d_H-i-s') . '.sqlite';
        $backupPath    = storage_path('app/' . $this->backupDir);

        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $destino = $backupPath . '/' . $nombreArchivo;

        if (!copy($dbPath, $destino)) {
            return back()->with('error', 'No se pudo copiar la base de datos. Verifica permisos.');
        }

        return response()->download($destino, $nombreArchivo, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }

    // ─── DESCARGAR UN RESPALDO DEL HISTORIAL ─────────────────────────────────
    public function download(string $nombre)
    {
        if (!preg_match('/^respaldo_[\d_\-]+\.sqlite$/', $nombre)) {
            abort(404);
        }

        $ruta = storage_path('app/' . $this->backupDir . '/' . $nombre);

        if (!file_exists($ruta)) {
            abort(404, 'Respaldo no encontrado.');
        }

        return response()->download($ruta, $nombre, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    // ─── RESTAURAR DESDE ARCHIVO SUBIDO ──────────────────────────────────────
    public function restore(Request $request)
    {
        $request->validate([
            'archivo_backup' => 'required|file|max:102400',
        ]);

        // Verificar extensión manualmente
        $ext = strtolower($request->file('archivo_backup')->getClientOriginalExtension());
        if (!in_array($ext, ['sqlite', 'db'])) {
            return back()->with('error', 'Solo se aceptan archivos .sqlite o .db');
        }

        $dbPath = database_path('database.sqlite');

        // 1. Antes de restaurar: hacer respaldo automático del estado actual
        $this->autoBackup($dbPath);

        // 2. Reemplazar la base de datos con el archivo subido
        $archivo = $request->file('archivo_backup');

        try {
            copy($archivo->getRealPath(), $dbPath);
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo restaurar: ' . $e->getMessage());
        }

        return redirect()->route('backup.index')
                         ->with('success', 'Base de datos restaurada correctamente. El sistema ahora muestra los datos del respaldo seleccionado.');
    }

    // ─── RESTAURAR DESDE HISTORIAL INTERNO ───────────────────────────────────
    public function restoreSaved(Request $request)
    {
        $request->validate(['nombre' => 'required|string']);

        $nombre = $request->nombre;

        if (!preg_match('/^respaldo_[\d_\-]+\.sqlite$/', $nombre)) {
            return back()->with('error', 'Nombre de archivo inválido.');
        }

        $origen = storage_path('app/' . $this->backupDir . '/' . $nombre);

        if (!file_exists($origen)) {
            return back()->with('error', 'El respaldo no existe en el sistema.');
        }

        $dbPath = database_path('database.sqlite');

        // Respaldo automático antes de restaurar
        $this->autoBackup($dbPath);

        try {
            copy($origen, $dbPath);
        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo restaurar: ' . $e->getMessage());
        }

        return redirect()->route('backup.index')
                         ->with('success', "Restaurado desde: {$nombre}. El sistema muestra los datos de ese respaldo.");
    }

    // ─── RESPALDO AUTOMÁTICO ANTES DE RESTAURAR ───────────────────────────────
    private function autoBackup(string $dbPath): void
    {
        if (!file_exists($dbPath)) return;

        $backupPath = storage_path('app/' . $this->backupDir);
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $nombreAuto = 'respaldo_auto_antes_restaurar_' . now()->format('Y-m-d_H-i-s') . '.sqlite';
        copy($dbPath, $backupPath . '/' . $nombreAuto);
    }

    // ─── HELPER ──────────────────────────────────────────────────────────────
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}