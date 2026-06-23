<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PostGrado - Iniciar Sesión</title>
    <link rel="stylesheet" href="/css/login.css">
</head>
<body class="login-body">
    @yield('content')
    <script src="/js/login.js"></script>
</body>
</html>