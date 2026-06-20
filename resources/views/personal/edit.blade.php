@extends('layouts.app')

@section('title', 'Editar Usuario')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/personal_edit.css') }}">
@endpush

@section('content')
<div class="personal-create">
    <h2>Editar Usuario</h2>

    <form method="POST" action="{{ route('personal.update', $personal) }}" class="form-personal">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="name" value="{{ old('name', $personal->name) }}" required>
        </div>

        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email', $personal->email) }}" required>
        </div>

        <div class="form-group">
            <label>Rol en el Sistema</label>
            <select name="role" required>
                <option value="admin" {{ old('role', $personal->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                <option value="operador" {{ old('role', $personal->role) == 'operador' ? 'selected' : '' }}>Operador</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="{{ route('personal.index') }}" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">Actualizar</button>
        </div>
    </form>
</div>
@endsection