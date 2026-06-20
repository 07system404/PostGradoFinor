<?php $__env->startSection('title', 'Editar Programa - PostGrado Pro'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/cursos_edit.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- Breadcrumb -->
<nav class="breadcrumb-perfil" style="margin-bottom: 16px;">
    <a href="<?php echo e(route('programas.index')); ?>">Programas Académicos</a>
    <span class="breadcrumb-sep">›</span>
    <span class="active">Editar: <?php echo e($programa->nombre); ?></span>
</nav>

<!-- Mensajes -->
<?php if($errors->any()): ?>
<div class="alert alert-error">
    <ul style="margin: 0; padding-left: 18px;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<!-- Formulario -->
<form id="form-nuevo-programa" action="<?php echo e(route('programas.update', $programa->id)); ?>" method="POST" class="form-programa-page">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>

    <!-- Top bar: title + action buttons -->
    <div class="form-top-bar">
        <div class="form-top-bar-left">
            <h2 class="form-top-title">Editar Programa Académico</h2>
            <p class="form-top-desc">Actualice los datos, costos y estructura académica del programa.</p>
        </div>
        <div class="form-top-actions">
            <a href="<?php echo e(route('programas.index')); ?>" class="btn-cancelar">Cancelar</a>
            <button type="submit" class="btn-guardar-programa">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span>Actualizar Programa</span>
            </button>
        </div>
    </div>

    <!-- Three columns -->
    <div class="form-three-columns">

        <!-- Columna 1: Datos Generales -->
        <div class="form-column">
            <div class="form-seccion">
                <div class="form-seccion-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    <h3>1. Datos Generales</h3>
                </div>
                <div class="form-seccion-body">
                    <div class="form-row-prog full">
                        <div class="form-group-prog">
                            <label for="nombre">Nombre del Curso / Programa</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Ej. Maestría en Ciencias de la Computación y Big Data"
                                   value="<?php echo e(old('nombre', $programa->nombre)); ?>" required>
                        </div>
                    </div>

                    <div class="form-row-prog">
                        <div class="form-group-prog">
                            <label for="tipo">Tipo de Programa</label>
                            <select id="tipo" name="tipo" required>
                                <option value="Diplomado" <?php echo e(old('tipo', $programa->tipo) == 'Diplomado' ? 'selected' : ''); ?>>Diplomado</option>
                                <option value="Especialidad" <?php echo e(old('tipo', $programa->tipo) == 'Especialidad' ? 'selected' : ''); ?>>Especialidad</option>
                                <option value="Maestría" <?php echo e(old('tipo', $programa->tipo) == 'Maestría' ? 'selected' : ''); ?>>Maestría</option>
                            </select>
                        </div>
                        <div class="form-group-prog">
                            <label for="version">Versión</label>
                            <input type="number" id="version" name="version" placeholder="Ej. 1"
                                   value="<?php echo e(old('version', $programa->version)); ?>" required min="1">
                        </div>
                        <div class="form-group-prog">
                            <label for="edicion">Edición</label>
                            <input type="number" id="edicion" name="edicion" placeholder="Ej. 2024"
                                   value="<?php echo e(old('edicion', $programa->edicion)); ?>" required min="1">
                        </div>
                    </div>

                    <div class="form-row-prog dos">
                        <div class="form-group-prog">
                            <label for="periodo">Periodo</label>
                            <div class="input-icon-wrapper">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <input type="text" id="periodo" name="periodo" placeholder="Ej. I-2026"
                                       value="<?php echo e(old('periodo', $programa->periodo)); ?>" required>
                            </div>
                        </div>
                        <div class="form-group-prog">
                            <label for="cupo">Cupo Máximo</label>
                            <input type="number" id="cupo" name="cupo" placeholder="0"
                                   value="<?php echo e(old('cupo', $programa->cupo)); ?>" required min="1">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna 2: Inversión Base -->
        <div class="form-column">
            <div class="form-seccion">
                <div class="form-seccion-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                        <line x1="2" y1="10" x2="22" y2="10"/>
                    </svg>
                    <h3>2. Inversión Base</h3>
                </div>
                <div class="form-seccion-body">
                    <div class="form-row-prog" style="grid-template-columns: 1fr;">
                        <div class="form-group-prog">
                            <label for="costo_matricula">Costo de Matrícula (Bs)</label>
                            <div class="input-moneda">
                                <span class="prefix">Bs</span>
                                <input type="number" id="costo_matricula" name="costo_matricula" step="0.01" min="0" placeholder="0.00"
                                       value="<?php echo e(old('costo_matricula', $programa->costo_matricula)); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-row-prog" style="grid-template-columns: 1fr;">
                        <div class="form-group-prog">
                            <label for="costo_total_estudio">Costo Total de Estudios (Bs)</label>
                            <div class="input-moneda">
                                <span class="prefix">Bs</span>
                                <input type="number" id="costo_total_estudio" name="costo_total_estudio" step="0.01" min="0" placeholder="0.00"
                                       value="<?php echo e(old('costo_total_estudio', $programa->costo_total_estudio)); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna 3: Estructura Académica y Defensas -->
        <div class="form-column">
            <div class="form-seccion">
                <div class="form-seccion-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    <h3>3. Estructura Académica y Defensas</h3>
                </div>
                <div class="form-seccion-body">

                    <!-- Diplomado -->
                    <div class="estructura-row">
                        <div class="estructura-level">
                            <span class="estructura-badge diplomado">Diplomado</span>
                        </div>
                        <div class="estructura-fields">
                            <div class="estructura-field">
                                <label>Módulos</label>
                                <div class="modulo-counter">
                                    <button type="button" class="btn-minus">−</button>
                                    <input type="number" id="nro_modulos_diplomado" name="nro_modulos_diplomado" value="<?php echo e(old('nro_modulos_diplomado', $programa->nro_modulos_diplomado)); ?>" min="1">
                                    <button type="button" class="btn-plus">+</button>
                                </div>
                            </div>
                            <div class="estructura-field">
                                <label>Costo Defensa (Bs)</label>
                                <div class="input-moneda">
                                    <span class="prefix">Bs</span>
                                    <input type="number" id="costo_defensa_diplomado" name="costo_defensa_diplomado" step="0.01" min="0" placeholder="0.00"
                                           value="<?php echo e(old('costo_defensa_diplomado', $programa->costo_defensa_diplomado)); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Especialidad -->
                    <div class="estructura-row">
                        <div class="estructura-level">
                            <span class="estructura-badge especialidad">Especialidad</span>
                        </div>
                        <div class="estructura-fields">
                            <div class="estructura-field">
                                <label>Módulos</label>
                                <div class="modulo-counter">
                                    <button type="button" class="btn-minus">−</button>
                                    <input type="number" id="nro_modulos_especialidad" name="nro_modulos_especialidad" value="<?php echo e(old('nro_modulos_especialidad', $programa->nro_modulos_especialidad)); ?>" min="0">
                                    <button type="button" class="btn-plus">+</button>
                                </div>
                            </div>
                            <div class="estructura-field">
                                <label>Costo Defensa (Bs)</label>
                                <div class="input-moneda">
                                    <span class="prefix">Bs</span>
                                    <input type="number" id="costo_defensa_especialidad" name="costo_defensa_especialidad" step="0.01" min="0" placeholder="0.00"
                                           value="<?php echo e(old('costo_defensa_especialidad', $programa->costo_defensa_especialidad)); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Maestría -->
                    <div class="estructura-row">
                        <div class="estructura-level">
                            <span class="estructura-badge maestria">Maestría</span>
                        </div>
                        <div class="estructura-fields">
                            <div class="estructura-field">
                                <label>Módulos</label>
                                <div class="modulo-counter">
                                    <button type="button" class="btn-minus">−</button>
                                    <input type="number" id="nro_modulos_maestria" name="nro_modulos_maestria" value="<?php echo e(old('nro_modulos_maestria', $programa->nro_modulos_maestria)); ?>" min="0">
                                    <button type="button" class="btn-plus">+</button>
                                </div>
                            </div>
                            <div class="estructura-field">
                                <label>Costo Defensa (Bs)</label>
                                <div class="input-moneda">
                                    <span class="prefix">Bs</span>
                                    <input type="number" id="costo_defensa_maestria" name="costo_defensa_maestria" step="0.01" min="0" placeholder="0.00"
                                           value="<?php echo e(old('costo_defensa_maestria', $programa->costo_defensa_maestria)); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- Estado -->
    <div class="form-seccion form-seccion-estado">
        <div class="form-seccion-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <h3>Estado del Programa</h3>
        </div>
        <div class="form-seccion-body">
            <div class="form-row-prog dos">
                <div class="form-group-prog">
                    <label for="activo">Estado</label>
                    <select id="activo" name="activo">
                        <option value="1" <?php echo e(old('activo', $programa->activo) ? 'selected' : ''); ?>>Activo</option>
                        <option value="0" <?php echo e(old('activo', $programa->activo) ? '' : 'selected'); ?>>Inactivo</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/cursos_form.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\PostGrado\resources\views/cursos/edit.blade.php ENDPATH**/ ?>