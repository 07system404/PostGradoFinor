<?php $__env->startSection('title', 'Gestión de Personal y Roles - PostGrado Pro'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/personal_index.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- Header -->
<div class="page-header" style="margin-bottom: 24px;">
    <h1>Personnel Management</h1>
</div>

<!-- Mensajes -->
<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
<div class="alert alert-error"><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="usuarios-layout">
    <!-- Panel Izquierdo: Formulario Registrar -->
    <div class="panel-formulario">
        <div class="panel-formulario-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="8.5" cy="7" r="4"/>
                <line x1="20" y1="8" x2="20" y2="14"/>
                <line x1="23" y1="11" x2="17" y2="11"/>
            </svg>
            <h3>Registrar Nuevo Usuario</h3>
        </div>

        <form id="form-registrar-usuario" action="<?php echo e(route('personal.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-usuario-group">
                <label for="name">Nombre Completo</label>
                <input type="text" id="name" name="name" placeholder="Carlos Mendoza" required>
            </div>

            <div class="form-usuario-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" placeholder="c.mendoza@postgrado-pro.edu" required>
            </div>

            <div class="form-usuario-group">
                <label for="rol">Rol en el Sistema</label>
                <select id="rol" name="role" required>
                    <option value="admin">Administrador</option>
                    <option value="operador">Operador</option>
                </select>
            </div>

            <div class="form-usuario-group">
                <label for="password">Contraseña Temporal</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="btn-toggle-password">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                <div class="password-hint">El usuario deberá cambiar la contraseña en su primer inicio de sesión.</div>
            </div>

            <button type="submit" class="btn-registrar-usuario">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <line x1="20" y1="8" x2="20" y2="14"/>
                    <line x1="23" y1="11" x2="17" y2="11"/>
                </svg>
                <span>Registrar Usuario</span>
            </button>
        </form>
    </div>

    <!-- Panel Derecho: Tabla de Personal -->
    <div class="panel-lista">
        <div class="lista-header">
            <h3>Personal con Acceso al Sistema</h3>
            <div class="lista-actions">
                <button class="btn-filtrar-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                    Filtrar
                </button>

            </div>
        </div>

        <table class="usuarios-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Última Actividad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $iniciales = collect(explode(' ', $usuario->name))
                            ->map(fn($n) => substr($n, 0, 1))
                            ->take(2)
                            ->implode('');
                        $claseRol = $usuario->role === 'admin' ? 'badge-admin' : 'badge-operador';
                        $claseAvatar = $usuario->role === 'admin' ? '' : 'operador';
                    ?>
                <tr>
                    <td>
                        <div class="usuario-info">
                            <div class="usuario-avatar <?php echo e($claseAvatar); ?>"><?php echo e($iniciales); ?></div>
                            <div class="usuario-datos">
                                <span class="usuario-nombre"><?php echo e($usuario->name); ?></span>
                                <span class="usuario-email"><?php echo e($usuario->email); ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-rol <?php echo e($claseRol); ?>"><?php echo e(strtoupper($usuario->role)); ?></span>
                    </td>
                    <td>
                        <span class="ultima-actividad"><?php echo e($usuario->updated_at->diffForHumans()); ?></span>
                    </td>
                    <td>
                        <div class="acciones-usuario">
                            <button class="btn-accion-user btn-editar-usuario" 
                                    data-user-id="<?php echo e($usuario->id); ?>"
                                    data-user-name="<?php echo e($usuario->name); ?>"
                                    data-user-email="<?php echo e($usuario->email); ?>"
                                    data-user-rol="<?php echo e($usuario->role); ?>"
                                    title="Editar">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <form action="<?php echo e(route('personal.destroy', $usuario->id)); ?>" method="POST" style="display: inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn-accion-user danger btn-eliminar-usuario" title="Eliminar">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--gray-500);">
                        No hay usuarios registrados en el sistema.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal-usuario-overlay" id="modal-editar-usuario">
    <div class="modal-usuario-container">
        <div class="modal-usuario-header">
            <h3>Editar Usuario</h3>
            <button type="button" class="modal-close-btn" id="cerrar-modal-editar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <form id="form-editar-usuario" action="<?php echo e(route('personal.update', 0)); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            <div class="form-usuario-group">
                <label for="edit-name">Nombre Completo</label>
                <input type="text" id="edit-name" name="name" required>
            </div>
            <div class="form-usuario-group">
                <label for="edit-email">Correo Electrónico</label>
                <input type="email" id="edit-email" name="email" required>
            </div>
            <div class="form-usuario-group">
                <label for="edit-rol">Rol en el Sistema</label>
                <select id="edit-rol" name="role" required>
                    <option value="admin">Administrador</option>
                    <option value="operador">Operador</option>
                </select>
            </div>
            <div class="form-usuario-group">
                <label for="edit-password">Nueva Contraseña (dejar en blanco para mantener)</label>
                <input type="password" id="edit-password" name="password" placeholder="••••••••">
            </div>
            <div class="modal-usuario-footer">
                <button type="button" class="btn-cancelar" id="cancelar-modal-editar">Cancelar</button>
                <button type="submit" class="btn-completar">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/personal_index.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PostGrado\resources\views/personal/index.blade.php ENDPATH**/ ?>