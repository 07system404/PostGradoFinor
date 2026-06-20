<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>PostGrado - <?php echo $__env->yieldContent('title'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/sidebar.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="app-body">

    <div class="app-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <svg class="logo-icon" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Mortarboard base -->
                    <polygon points="24,8 44,18 24,28 4,18" fill="white" opacity="0.95"/>
                    <path d="M24 28 L44 18 L44 30 Q44 32 24 38 Q4 32 4 30 L4 18 Z" fill="white" opacity="0.7"/>
                    <!-- Cap top -->
                    <rect x="41" y="18" width="3" height="12" rx="1.5" fill="white" opacity="0.8"/>
                    <circle cx="42.5" cy="31" r="2" fill="white" opacity="0.8"/>
                    <!-- Pluma -->
                    <path d="M30 10 Q36 4 40 6 Q38 12 30 14 Z" fill="white" opacity="0.9"/>
                    <path d="M30 10 L34 8" stroke="#1a3fa8" stroke-width="0.8" stroke-linecap="round"/>
                </svg>
                <span class="logo-text">PostGrado</span>
            </div>

            <nav class="sidebar-nav">
                <a href="<?php echo e(route('dashboard')); ?>" class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo e(route('estudiantes.index')); ?>" class="nav-item <?php echo e(request()->routeIs('estudiantes.*') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Gestión de Alumnos</span>
                </a>
                <a href="<?php echo e(route('caja.index')); ?>" class="nav-item <?php echo e(request()->routeIs('caja.*') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <span>Caja y Finanzas</span>
                </a>
                <a href="<?php echo e(route('programas.index')); ?>" class="nav-item <?php echo e(request()->routeIs('programas.*') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span>Cursos</span>
                </a>
                <?php if(auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('personal.index')); ?>" class="nav-item <?php echo e(request()->routeIs('personal.*') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Personal</span>
                </a>
                <?php endif; ?>
                <?php if(auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('reportes')); ?>" class="nav-item <?php echo e(request()->routeIs('reportes*') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <span>Reportes</span>
                </a>
                <a href="<?php echo e(route('backup.index')); ?>" class="nav-item <?php echo e(request()->routeIs('backup*') ? 'active' : ''); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Respaldo</span>
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <div class="page-header">
                <h1 class="page-title"><?php echo $__env->yieldContent('title'); ?></h1>
            </div>
            <div class="page-body">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>

    </div>

    <script src="<?php echo e(asset('js/app.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\laragon\www\PostGrado\resources\views/layouts/app.blade.php ENDPATH**/ ?>