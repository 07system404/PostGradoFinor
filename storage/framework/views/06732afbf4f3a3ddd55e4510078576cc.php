

<?php $__env->startSection('title', 'Gestión de Alumnos'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/estudiantes.css')); ?>">
<style>
/* === BARRA DE BÚSQUEDA AZUL (ESTILO CURSOS) === */
.programas-search-bar {
    flex-shrink: 0;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
    border: 1px solid #bfdbfe !important;
    border-radius: 16px !important;
    padding: 16px 20px !important;
    margin-bottom: 20px !important;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08) !important;
}
.search-bar-form {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    flex-wrap: wrap !important;
}
.search-bar-input-group {
    position: relative !important;
    flex: 1 !important;
    min-width: 200px !important;
}
.search-bar-icon {
    position: absolute !important;
    left: 14px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: #3b82f6 !important;
    pointer-events: none !important;
}
.search-bar-input-group input {
    width: 100% !important;
    padding: 11px 16px 11px 42px !important;
    border: 1.5px solid #bfdbfe !important;
    border-radius: 12px !important;
    font-size: 14px !important;
    color: #1e293b !important;
    background: #ffffff !important;
    transition: all 0.25s ease !important;
    outline: none !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
}
.search-bar-input-group input:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 2px 8px rgba(59, 130, 246, 0.1) !important;
}
.search-bar-input-group input::placeholder {
    color: #93c5fd !important;
}
.search-bar-select-group {
    position: relative !important;
    display: flex !important;
    align-items: center !important;
}
.search-bar-filter-icon {
    position: absolute !important;
    left: 14px !important;
    color: #3b82f6 !important;
    pointer-events: none !important;
    z-index: 1 !important;
}
.filter-estado {
    padding: 11px 42px 11px 38px !important;
    border: 1.5px solid #bfdbfe !important;
    border-radius: 12px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: #1e4b8a !important;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2.5'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center !important;
    appearance: none !important;
    cursor: pointer !important;
    outline: none !important;
    min-width: 220px !important;
}
.btn-nuevo-programa {
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 24px !important;
    border: none !important;
    border-radius: 12px !important;
    background: #3b82f6 !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3) !important;
    white-space: nowrap !important;
}
.btn-nuevo-programa:hover {
    background: #2563eb !important;
    transform: translateY(-1px) !important;
}
.btn-nuevo-programa svg {
    width: 18px !important;
    height: 18px !important;
}
@media (max-width: 1024px) {
    .search-bar-form {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    .search-bar-input-group {
        max-width: 100% !important;
    }
    .filter-estado {
        width: 100% !important;
    }
    .btn-nuevo-programa {
        justify-content: center !important;
    }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- Mensajes -->
<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert alert-error"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<!-- Barra de búsqueda -->
<div class="programas-search-bar">
    <form id="form-buscar-alumnos" action="<?php echo e(route('estudiantes.index')); ?>" method="GET" class="search-bar-form">
        <div class="search-bar-input-group">
            <svg class="search-bar-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" id="buscar-alumno" placeholder="Buscar alumnos por nombre o registro..." 
                   value="<?php echo e(request('search')); ?>">
        </div>
        <div class="search-bar-select-group">
            <svg class="search-bar-filter-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="6" x2="20" y2="6"/>
                <line x1="8" y1="12" x2="20" y2="12"/>
                <line x1="12" y1="18" x2="20" y2="18"/>
            </svg>
            <select id="filtro-curso" name="curso_id" class="filter-estado">
                <option value="">Filtrar por Programa/Curso</option>
                <?php $__currentLoopData = $cursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $curso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($curso->id); ?>" <?php echo e(request('curso_id') == $curso->id ? 'selected' : ''); ?>>
                    <?php echo e($curso->nombre); ?>

                </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <a href="<?php echo e(route('estudiantes.create')); ?>" class="btn-nuevo-programa">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            + Inscripción Rápida
        </a>
    </form>
</div>

<!-- Card Table -->
<div class="card-table">
    <div class="card-table-header">
        <div class="cohorte-info">
            <h3>Cohorte de Alumnos Activos</h3>
            <span class="badge-total">TOTAL: <?php echo e($totalAlumnos); ?></span>
            <span class="badge-solventes">SOLVENTES: <?php echo e($solventes); ?></span>
        </div>
        <button class="btn-filter" title="Filtros avanzados">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="4" y1="21" x2="4" y2="14"/>
                <line x1="4" y1="10" x2="4" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12" y2="3"/>
                <line x1="20" y1="21" x2="20" y2="16"/>
                <line x1="20" y1="12" x2="20" y2="3"/>
                <line x1="1" y1="14" x2="7" y2="14"/>
                <line x1="9" y1="8" x2="15" y2="8"/>
                <line x1="17" y1="16" x2="23" y2="16"/>
            </svg>
        </button>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>CÓDIGO REG.</th>
                    <th>ALUMNO</th>
                    <th>PROGRAMA ACADÉMICO</th>
                    <th>ESTADO</th>
                    <th>DOCUMENTACIÓN</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $estudiantes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estudiante): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $inscripcion = $estudiante->inscripciones->first();
                        $programaPrincipal = $inscripcion ? $inscripcion->curso->nombre : 'Sin inscripción';
                        $estadoAcademico = $inscripcion ? $inscripcion->estado_academico : 'Sin Inscripción';

                        $claseEstado = match($estadoAcademico) {
                            'Activo' => 'estado-activo',
                            'Pendiente' => 'estado-pendiente',
                            'Mora' => 'estado-mora',
                            'Congelado' => 'estado-congelado',
                            'Sin Inscripción' => 'estado-pendiente',
                            default => 'estado-pendiente',
                        };

                        $totalDocs = $estudiante->documentos->count();
                        $docCompletado = $totalDocs >= 3;
                    ?>
                <tr>
                    <td data-label="Código Reg.">
                        <span class="codigo-reg"><?php echo e($estudiante->registro); ?></span>
                    </td>
                    <td data-label="Alumno">
                        <span class="nombre-alumno"><?php echo e($estudiante->nombre_completo); ?></span>
                    </td>
                    <td data-label="Programa">
                        <div class="programa-principal"><?php echo e($programaPrincipal); ?></div>                            <?php if($estudiante->inscripciones->count() > 1): ?>
                            <?php $__currentLoopData = $estudiante->inscripciones->skip(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="programa-secundario"><?php echo e(strtoupper($insc->curso?->nombre ?? 'N/A')); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                    </td>
                    <td data-label="Estado">
                        <span class="estado-badge <?php echo e($claseEstado); ?>">
                            <?php if($estadoAcademico == 'Activo'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            <?php elseif($estadoAcademico == 'Pendiente'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <circle cx="12" cy="12" r="10"/>
                            </svg>
                            <?php elseif($estadoAcademico == 'Mora'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                            <?php endif; ?>
                            <?php echo e(strtoupper($estadoAcademico)); ?>

                        </span>
                    </td>
                    <td data-label="Documentación">
                        <div class="doc-status <?php echo e($docCompletado ? 'completado' : 'pendiente'); ?>">
                            <?php if($docCompletado): ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <?php else: ?>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <?php endif; ?>
                            <span><?php echo e($docCompletado ? '3/3 COMPLETADO' : $totalDocs . '/3 PENDIENTE'); ?></span>
                        </div>
                        <button class="btn-subir-doc" data-alumno="<?php echo e($estudiante->nombre_completo); ?>" data-href="#">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            <span>SUBIR</span>
                        </button>
                    </td>
                    <td data-label="Acciones">
                        <div class="acciones">
                            <a href="<?php echo e(route('estudiantes.show', $estudiante->id)); ?>" class="btn-accion" title="Ver detalle">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <button class="btn-accion" title="Eliminar" data-confirm="¿Está seguro de eliminar este alumno?">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--gray-500);">
                        No se encontraron alumnos registrados.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-footer">
        <span class="pagination-info">
            Mostrando <?php echo e($estudiantes->firstItem() ?? 0); ?> a <?php echo e($estudiantes->lastItem() ?? 0); ?> de <?php echo e($estudiantes->total()); ?> alumnos
        </span>
        <div class="pagination-controls">
            <?php echo e($estudiantes->links()); ?>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/estudiante.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PostGrado\resources\views/estudiantes/index.blade.php ENDPATH**/ ?>