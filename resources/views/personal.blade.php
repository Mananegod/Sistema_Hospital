@extends('layouts.app')

@section('title', 'Gestión de Personal')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Personal</h1>
            <p class="text-slate-500 mt-1">Médicos, enfermeros, equipo técnico y administradores.</p>
        </div>

        <div class="inline-flex p-1 bg-slate-200/60 rounded-sm">
            <a href="{{ route('personal.index', ['tipo' => 'Usuario']) }}"
               class="px-5 py-2 text-sm font-bold rounded-sm transition {{ $tipo === 'Usuario' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Usuarios estándar
            </a>
            <a href="{{ route('personal.index', ['tipo' => 'Admin']) }}"
               class="px-5 py-2 text-sm font-bold rounded-sm transition {{ $tipo === 'Admin' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Administradores
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-sm shadow-sm">
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Formulario de Registro --}}
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm sticky top-6">
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-sm"></span> Nuevo Registro
                </h2>
                <form action="{{ route('personal.store') }}" method="POST" class="space-y-4"
                    x-on:submit.prevent="$store.loading.submitForm($event.target)">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Cédula</label>
                        <input type="text" name="cedula" value="{{ old('cedula') }}" placeholder="Cédula" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Nombres</label>
                            <input type="text" name="nombres" value="{{ old('nombres') }}" placeholder="Nombres" required
                                class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Apellidos</label>
                            <input type="text" name="apellidos" value="{{ old('apellidos') }}" placeholder="Apellidos"
                                required class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Usuario (Acceso)</label>
                        <select name="tipo_usuario" class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow font-semibold text-slate-700">
                            <option value="Usuario" {{ old('tipo_usuario', $tipo) == 'Usuario' ? 'selected' : '' }}>Usuario</option>
                            <option value="Admin" {{ old('tipo_usuario', $tipo) == 'Admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Cargo</label>
                        <select name="cargo" class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow">
                            <option value="" disabled {{ old('cargo') ? '' : 'selected' }}>Seleccione cargo</option>
                            <option value="Médico" {{ old('cargo') == 'Médico' ? 'selected' : '' }}>Médico</option>
                            <option value="Enfermero/a" {{ old('cargo') == 'Enfermero/a' ? 'selected' : '' }}>Enfermero/a</option>
                            <option value="Administrativo" {{ old('cargo') == 'Administrativo' ? 'selected' : '' }}>Administrativo</option>
                            <option value="Mantenimiento" {{ old('cargo') == 'Mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Turno</label>
                        <select name="turno" class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow">
                            <option value="" disabled {{ old('turno') ? '' : 'selected' }}>Turno</option>
                            <option value="Mañana" {{ old('turno') == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                            <option value="Tarde" {{ old('turno') == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                            <option value="Noche" {{ old('turno') == 'Noche' ? 'selected' : '' }}>Noche</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="Teléfono" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow">
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-3 rounded-sm shadow-md hover:bg-slate-800 transition">
                        Registrar Personal
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabla de Personal Filtrada --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
                        Mostrando: <strong class="text-slate-800">{{ $tipo }}s</strong>
                    </span>
                    <span class="text-xs text-slate-400 font-semibold">Total: {{ $personal->count() }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[500px]">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase border-b">
                            <tr>
                                <th class="px-6 py-4">Nombre y Cargo</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($personal as $p)
                            <tr class="{{ !$p->activo ? 'opacity-50 grayscale' : '' }} hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5">
                                    <p class="font-bold text-slate-800">{{ $p->nombres }} {{ $p->apellidos }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-blue-600 font-semibold uppercase">{{ $p->cargo }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase {{ $p->tipo_usuario === 'Admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $p->tipo_usuario }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="px-3 py-1 rounded-sm text-xs font-bold 
                                        {{ $p->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $p->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="$store.modal.open('viewPerson', {{ Js::from($p) }})"
                                            class="p-2 text-slate-500 hover:bg-slate-100 rounded-sm transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                        @if($p->activo)
                                        <button @click="$store.modal.open('editPerson', {{ Js::from($p) }})"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-sm transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        @endif

                                        <form :id="'status-form-{{ $p->id }}'"
                                            action="{{ route('personal.status', $p->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf @method('PATCH')
                                        </form>
                                        <button type="button" @click="confirmAction(
                                                '{{ $p->activo ? 'Desactivar' : 'Reactivar' }} personal',
                                                '¿Seguro que quieres {{ $p->activo ? 'desactivar' : 'reactivar' }} a {{ $p->nombres }} {{ $p->apellidos }}?',
                                                'status-form-{{ $p->id }}'
                                            )"
                                            class="p-2 {{ $p->activo ? 'text-red-500 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-sm transition">
                                            @if($p->activo)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                </path>
                                            </svg>
                                            @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                </path>
                                            </svg>
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                    No hay personal registrado en la categoría de {{ $tipo }}s.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ver Personal --}}
<x-modal id="viewPerson" title="Ficha del Empleado" maxWidth="max-w-md">
    <div class="space-y-3">
        <p><strong>Cédula:</strong> <span x-text="$store.modal.data.cedula"></span></p>
        <p><strong>Nombre:</strong> <span x-text="$store.modal.data.nombres + ' ' + $store.modal.data.apellidos"></span></p>
        <p><strong>Tipo de Usuario:</strong> <span class="font-bold text-purple-600" x-text="$store.modal.data.tipo_usuario"></span></p>
        <p><strong>Cargo:</strong> <span class="text-blue-600 font-bold" x-text="$store.modal.data.cargo"></span></p>
        <p><strong>Turno:</strong> <span x-text="$store.modal.data.turno"></span></p>
        <p><strong>Teléfono:</strong> <span x-text="$store.modal.data.telefono"></span></p>
    </div>
    <div class="mt-6">
        <button @click="$store.modal.close()"
            class="w-full bg-slate-900 text-white font-bold py-3 rounded-sm shadow">Cerrar</button>
    </div>
</x-modal>

{{-- Modal Editar Personal --}}
<x-modal id="editPerson" title="Editar Información" maxWidth="max-w-md">
    <form :action="'/personal/' + $store.modal.data.id" method="POST"
        x-on:submit.prevent="$store.loading.submitForm($event.target)">
        @csrf
        @method('PUT')
        
        <label class="block text-xs font-bold text-slate-500 mb-1">Nombres</label>
        <input type="text" name="nombres" x-model="$store.modal.data.nombres"
            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow mb-4">
            
        <label class="block text-xs font-bold text-slate-500 mb-1">Apellidos</label>
        <input type="text" name="apellidos" x-model="$store.modal.data.apellidos"
            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow mb-4">

        <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Usuario</label>
        <select name="tipo_usuario" x-model="$store.modal.data.tipo_usuario"
            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow mb-4 font-semibold text-slate-700">
            <option value="Usuario">Usuario</option>
            <option value="Admin">Administrador</option>
        </select>

        <label class="block text-xs font-bold text-slate-500 mb-1">Cargo</label>
        <select name="cargo" x-model="$store.modal.data.cargo"
            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 input-shadow mb-4">
            <option value="Médico">Médico</option>
            <option value="Enfermero/a">Enfermero/a</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select>

        <div class="flex gap-3 pt-2">
            <button type="button" @click="$store.modal.close()"
                class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-sm">Cancelar</button>
            <button type="submit"
                class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-sm shadow">Actualizar</button>
        </div>
    </form>
</x-modal>
@endsection