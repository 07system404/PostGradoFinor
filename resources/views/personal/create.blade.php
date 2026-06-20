@extends('layouts.app')

@section('title', 'Registrar Nuevo Usuario')

@section('content')

<link rel="stylesheet" href="{{ asset('css/personal_create.css') }}">
<div class="personal-create">
    <h2>Registrar Nuevo Usuario</h2>
    <p class="subtitle">Complete el formulario para dar acceso al sistema.</p>

    @if ($errors->any())
        <div class="alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('personal.store') }}" class="form-personal">
        @csrf

        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Carlos Mendoza" required>
        </div>

        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="c.mendoza@postgrado-pro.edu" required>
        </div>

        <div class="form-group">
            <label>Rol en el Sistema</label>
            <select name="role" required>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="operador" {{ old('role') == 'operador' ? 'selected' : '' }}>Operador</option>
            </select>
        </div>

        <div class="form-group">
            <label>Contraseña Temporal</label>
            <div class="password-display">
                <span id="tempPassword">**********</span>
                <button type="button" id="generatePassword" class="btn-generate">Generar</button>
            </div>
            <input type="hidden" name="password" id="passwordInput" value="">
            <p class="help-text">El usuario deberá cambiar la contraseña en su primer inicio de sesión.</p>
        </div>

        <div class="form-actions">
            <a href="{{ route('personal.index') }}" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">Registrar Usuario</button>
        </div>
    </form>
</div>
<script src="{{ asset('js/personal_form.js') }}"></script>

@endsection