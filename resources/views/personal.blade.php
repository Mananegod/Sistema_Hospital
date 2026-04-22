@extends('layouts.app')

@section('title', 'Gestión de Personal')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Personal</h1>
        <p class="text-slate-500 mt-1">Médicos, enfermeros y equipo técnico del hospital.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm">
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm sticky top-6">
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Nuevo Registro
                </h2>
                <form action="{{ route('personal.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="text" name="cedula" value="{{ old('cedula') }}" placeholder="Cédula" required
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="nombres" value="{{ old('nombres') }}" placeholder="Nombres" required
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                        <input type="text" name="apellidos" value="{{ old('apellidos') }}" placeholder="Apellidos"
                            required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                    </div>
                    <select name="cargo" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                        <option value="" disabled {{ old('cargo') ? '' : 'selected' }}>Seleccione cargo</option>
                        <option value="Médico" {{ old('cargo') == 'Médico' ? 'selected' : '' }}>Médico</option>
                        <option value="Enfermero/a" {{ old('cargo') == 'Enfermero/a' ? 'selected' : '' }}>Enfermero/a
                        </option>
                        <option value="Administrativo" {{ old('cargo') == 'Administrativo' ? 'selected' : '' }}>
                            Administrativo</option>
                        <option value="Mantenimiento" {{ old('cargo') == 'Mantenimiento' ? 'selected' : '' }}>
                            Mantenimiento</option>
                    </select>
                    <select name="turno" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                        <option value="" disabled {{ old('turno') ? '' : 'selected' }}>Turno</option>
                        <option value="Mañana" {{ old('turno') == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                        <option value="Tarde" {{ old('turno') == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                        <option value="Noche" {{ old('turno') == 'Noche' ? 'selected' : '' }}>Noche</option>
                    </select>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" placeholder="Teléfono" required
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl shadow-md hover:bg-slate-800 transition">Registrar
                        Personal</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
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
                                    <p class="text-xs text-blue-600 font-semibold uppercase">{{ $p->cargo }}</p>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="px-3 py-1 rounded-lg text-xs font-bold {{ $p->activo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $p->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="$store.modal.open('viewPerson', {{ Js::from($p) }})"
                                            class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                        @if($p->activo)
                                        <button @click="$store.modal.open('editPerson', {{ Js::from($p) }})"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        @endif
                                        {{-- Formulario oculto para cambiar estado --}}
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
                                            class="p-2 {{ $p->activo ? 'text-red-500 hover:bg-red-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition">
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
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">No hay personal
                                    registrado.</td>
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
        <p><strong>Nombre:</strong> <span x-text="$store.modal.data.nombres + ' ' + $store.modal.data.apellidos"></span>
        </p>
        <p><strong>Cargo:</strong> <span class="text-blue-600 font-bold" x-text="$store.modal.data.cargo"></span></p>
        <p><strong>Turno:</strong> <span x-text="$store.modal.data.turno"></span></p>
        <p><strong>Teléfono:</strong> <span x-text="$store.modal.data.telefono"></span></p>
    </div>
    <div class="mt-6">
        <button @click="$store.modal.close()"
            class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl shadow">Cerrar</button>
    </div>
</x-modal>

{{-- Modal Editar Personal --}}
<x-modal id="editPerson" title="Editar Información" maxWidth="max-w-md">
    <form :action="'/personal/' + $store.modal.data.id" method="POST">
        @csrf
        @method('PUT')
        <input type="text" name="nombres" x-model="$store.modal.data.nombres"
            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow mb-4">
        <input type="text" name="apellidos" x-model="$store.modal.data.apellidos"
            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow mb-4">
        <select name="cargo" x-model="$store.modal.data.cargo"
            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow mb-4">
            <option value="Médico">Médico</option>
            <option value="Enfermero/a">Enfermero/a</option>
            <option value="Administrativo">Administrativo</option>
            <option value="Mantenimiento">Mantenimiento</option>
        </select>
        <div class="flex gap-3 pt-2">
            <button type="button" @click="$store.modal.close()"
                class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">Cancelar</button>
            <button type="submit"
                class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl shadow">Actualizar</button>
        </div>
    </form>
</x-modal>
@endsection
