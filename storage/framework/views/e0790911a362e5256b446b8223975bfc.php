

<?php $__env->startSection('title', 'Respaldo del Sistema'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/backup.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="backup-header">
    <h1 class="page-h1">Respaldo del Sistema</h1>
    <p class="page-desc">Genera, descarga y restaura la base de datos desde el panel.</p>
</div>

<?php if(session('success')): ?>
    <div class="alert-success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div class="alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<div class="backup-grid">

    
    <div class="backup-card">
        <div class="backup-card-icon backup-card-icon--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <ellipse cx="12" cy="5" rx="9" ry="3"/>
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
            </svg>
        </div>
        <div class="backup-card-body">
            <h3>Generar Respaldo</h3>
            <p>Descarga una copia del archivo <strong>database.sqlite</strong> con todos los datos actuales. Guárdala en tu computadora o USB.</p>
            <div class="backup-meta">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Último respaldo: <?php echo e($ultimoRespaldo ?? 'Nunca'); ?>

                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Tamaño actual: <?php echo e($tamanoDb ?? '—'); ?>

                </span>
            </div>
            <a href="<?php echo e(route('backup.generate')); ?>" class="btn-backup-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Descargar Respaldo (.sqlite)
            </a>
        </div>
    </div>

    
    <div class="backup-card backup-card--restore">
        <div class="backup-card-icon backup-card-icon--orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
            </svg>
        </div>
        <div class="backup-card-body">
            <h3>Restaurar Respaldo</h3>
            <p>Sube un archivo <strong>.sqlite</strong> que hayas descargado antes. El sistema reemplazará la base de datos actual con ese archivo y <strong>todo volverá al estado de ese respaldo</strong>.</p>

            <div class="restore-warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>Los datos actuales serán reemplazados. Esta acción no se puede deshacer a menos que tengas otro respaldo.</span>
            </div>

            <form method="POST" action="<?php echo e(route('backup.restore')); ?>" enctype="multipart/form-data" id="restoreForm">
                <?php echo csrf_field(); ?>
                
                <input type="file" name="archivo_backup" id="archivoBackup" accept=".sqlite" required style="display:none">

                <div class="file-upload-area" id="dropZone">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="16 16 12 12 8 16"/>
                        <line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                    <p id="uploadLabel">Haz clic o arrastra tu archivo <strong>.sqlite</strong> aquí</p>
                    <span id="fileName" class="file-name-preview"></span>
                </div>

                <button type="button" class="btn-restore" onclick="confirmarRestaurar()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
                    </svg>
                    Restaurar Base de Datos
                </button>
            </form>
        </div>
    </div>

</div>


<?php if(count($historial) > 0): ?>
<div class="backup-historial">
    <h2 class="historial-title">Respaldos Guardados en el Sistema</h2>
    <div class="historial-table-wrap">
        <table class="historial-table">
            <thead>
                <tr>
                    <th>ARCHIVO</th>
                    <th>FECHA</th>
                    <th>TAMAÑO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="hist-file">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                            <?php echo e($item['nombre']); ?>

                        </div>
                    </td>
                    <td><?php echo e($item['fecha']); ?></td>
                    <td><?php echo e($item['tamaño']); ?></td>
                    <td>
                        <div style="display:flex;gap:8px;align-items:center;">
                            
                            <a href="<?php echo e(route('backup.download', $item['nombre'])); ?>" class="btn-hist-action btn-hist-download" title="Descargar">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                Descargar
                            </a>
                            
                            <form method="POST" action="<?php echo e(route('backup.restoreSaved')); ?>" onsubmit="return confirm('¿Restaurar desde este respaldo? Los datos actuales serán reemplazados.')">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="nombre" value="<?php echo e($item['nombre']); ?>">
                                <button type="submit" class="btn-hist-action btn-hist-restore" title="Restaurar este respaldo">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>
                                    Restaurar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<div class="modal-overlay" id="modalConfirm" style="display:none;">
    <div class="modal-box">
        <div class="modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <h3>¿Confirmar restauración?</h3>
        <p>Los datos actuales del sistema serán <strong>reemplazados completamente</strong> por el archivo seleccionado. Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
            <button onclick="cerrarModal()" class="btn-modal-cancel">Cancelar</button>
            <button onclick="document.getElementById('restoreForm').submit()" class="btn-modal-confirm">Sí, restaurar ahora</button>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/backup.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PostGrado\resources\views/backup/index.blade.php ENDPATH**/ ?>