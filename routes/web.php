<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\CajaController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CursoController;
use App\Http\Controllers\Web\DocumentoController;
use App\Http\Controllers\Web\EstudianteController;
use App\Http\Controllers\Web\ReporteController;
use App\Http\Controllers\Web\InscripcionController;
use App\Http\Controllers\Web\PagoController;
use App\Http\Controllers\Web\BackupController;
use App\Http\Controllers\Web\PersonalController;
use Illuminate\Support\Facades\Route;

// 1. AUTENTICACIÓN (público)
Route::get('login', [LoginController::class, 'showForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// 2. REDIRECCIÓN RAIZ
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 3. RUTAS PROTEGIDAS (autenticación requerida)
Route::middleware(['auth'])->group(function () {

    //  Dashboard 
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //  Gestión de Estudiantes 
    Route::prefix('estudiantes')->name('estudiantes.')->group(function () {
    Route::get('/', [EstudianteController::class, 'index'])->name('index');
    Route::get('/create', [EstudianteController::class, 'create'])->name('create');
    Route::post('/store', [EstudianteController::class, 'store'])->name('store');
    Route::get('/{estudiante}', [EstudianteController::class, 'show'])->name('show');
    Route::put('/{estudiante}', [EstudianteController::class, 'update'])->name('update');
    Route::post('/{estudiante}/inscribir', [EstudianteController::class, 'inscribirCurso'])->name('inscribir');
    Route::delete('/{estudiante}', [EstudianteController::class, 'destroy'])->name('destroy');
    Route::post('/{estudiante}/reactivar', [EstudianteController::class, 'reactivate'])->name('reactivate');
});
    //  Inscripciones (desde detalle) 
    Route::get('estudiantes/{estudiante}/inscripciones/create', [InscripcionController::class, 'create'])
        ->name('inscripciones.create');
    Route::post('estudiantes/{estudiante}/inscripciones', [InscripcionController::class, 'store'])
        ->name('inscripciones.store');

    //  Documentos 
    Route::post('estudiantes/{estudiante}/documentos', [DocumentoController::class, 'store'])
        ->name('documentos.store');
    Route::get('documentos/{documento}/descargar', [DocumentoController::class, 'descargar'])
        ->name('documentos.descargar');
    Route::delete('documentos/{documento}', [DocumentoController::class, 'destroy'])
        ->name('documentos.destroy');

    //  Programas Académicos 
    Route::resource('programas', CursoController::class);
    Route::get('programas/buscar/ajax', [CursoController::class, 'buscarAjax'])->name('programas.buscar.ajax');
    Route::put('programas/{programa}/inactivar', [CursoController::class, 'toggleActivo'])->name('programas.inactivar');
    Route::post('programas/{programa}/inscribir-estudiante', [CursoController::class, 'inscribirEstudiante'])->name('programas.inscribir');
    Route::delete('programas/{programa}/desinscribir/{inscripcion}', [CursoController::class, 'desinscribirEstudiante'])->name('programas.desinscribir');

    //  Reportes (Solo Admin) 
    Route::middleware('role:admin')->group(function () {
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
        Route::get('/reportes/estudiantes', [EstudianteController::class, 'exportar'])->name('reportes.estudiantes');
        Route::get('/reportes/programas', [CursoController::class, 'exportar'])->name('reportes.programas');
        Route::get('/reportes/cursos/{curso}/exportar', [ReporteController::class, 'exportarCursos'])->name('reportes.cursos.exportar');
        Route::get('/reportes/exportar-planilla/{curso}', [ReporteController::class, 'exportarCursos'])->name('reportes.exportar.planilla');
        Route::get('/reportes/pagos-rango', [ReporteController::class, 'exportarPagosRango'])->name('reportes.pagos.rango');

        //  Respaldo / Backup 
        Route::prefix('backup')->name('backup.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::post('/generar', [BackupController::class, 'generate'])->name('generate');
            Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
            Route::get('/descargar/{nombre}', [BackupController::class, 'download'])->name('download');
            Route::post('/restore-saved', [BackupController::class, 'restoreSaved'])->name('restoreSaved');
        });

        //  Personal
        Route::resource('personal', PersonalController::class);
    });

    //  Caja y Finanzas 
    Route::resource('caja', CajaController::class)->only(['index']);
    Route::get('caja/pago/buscar-alumnos', [CajaController::class, 'buscarAlumnos'])->name('caja.pago.buscar');
    Route::get('caja/pago/detalles/{inscripcion}', [CajaController::class, 'detallesPorInscripcion'])->name('caja.pago.detalles');
    Route::get('caja/{estudiante}/pago/{detalle}', [PagoController::class, 'create'])->name('caja.pago.create');
    Route::post('caja/pago', [PagoController::class, 'store'])->name('caja.pago.store');
    Route::get('caja/pago/formulario/{detalle?}', [CajaController::class, 'formularioPago'])->name('caja.pago.formulario');
    Route::get('caja/recibo/{detalle}', [CajaController::class, 'recibo'])->name('caja.pago.recibo');

}); 