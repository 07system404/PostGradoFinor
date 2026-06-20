<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>PostGrado - Iniciar Sesión</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
</head>
<body class="login-body">
    <?php echo $__env->yieldContent('content'); ?>
    <script src="<?php echo e(asset('js/login.js')); ?>"></script>
</body>
</html><?php /**PATH C:\laragon\www\PostGrado\resources\views/layouts/login.blade.php ENDPATH**/ ?>