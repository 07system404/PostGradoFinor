@extends('layouts.login')

@section('content')
<div class="login-wrapper">

    <!-- Panel izquierdo -->
    <div class="login-left">
        <div class="login-brand">
            <div class="login-logo">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="24,8 44,18 24,28 4,18" fill="white" opacity="0.95"/>
                    <path d="M24 28 L44 18 L44 30 Q44 32 24 38 Q4 32 4 30 L4 18 Z" fill="white" opacity="0.7"/>
                    <rect x="41" y="18" width="3" height="12" rx="1.5" fill="white" opacity="0.8"/>
                    <circle cx="42.5" cy="31" r="2" fill="white" opacity="0.8"/>
                    <path d="M30 10 Q36 4 40 6 Q38 12 30 14 Z" fill="white" opacity="0.9"/>
                    <path d="M30 10 L34 8" stroke="#1a3fa8" stroke-width="0.8" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="brand-name">PostGrado</h1>
            <p class="brand-sub">Sistema de Gestión Académica</p>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="login-right">
        <div class="login-box">
            <h2 class="login-title">Bienvenido</h2>
            <p class="login-sub">Ingresa tus credenciales para continuar</p>

            @if($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login" id="loginForm">
                @csrf

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input type="email" id="email" name="email" placeholder="usuario@dominio.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="toggle-pw" onclick="togglePassword()">
                            <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>
        </div>
    </div>

</div>
@endsection