@extends('layouts.app')

@section('title', 'Inventario de Medicamentos')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Módulo de Inventario</h1>
        <p class="text-slate-500 mt-1">Hospital Dr. Tiburcio Garrido</p>
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
                <form action="{{ route('medicamentos.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="text" name="codigo_lote" value="{{ old('codigo_lote') }}" placeholder="Código de Lote"
                        required
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="nombre_medicamento" value="{{ old('nombre_medicamento') }}"
                        placeholder="Nombre del medicamento" required
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="cantidad_stock" value="{{ old('cantidad_stock') }}"
                        placeholder="Cantidad en stock" required
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow focus:ring-2 focus:ring-blue-500">
                    <select name="area_destino" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                        <option value="Urgencias" {{ old('area_destino') == 'Urgencias' ? 'selected' : '' }}>Urgencias
                        </option>
                        <option value="Pediatría" {{ old('area_destino') == 'Pediatría' ? 'selected' : '' }}>Pediatría
                        </option>
                        <option value="UCI" {{ old('area_destino') == 'UCI' ? 'selected' : '' }}>UCI</option>
                        <option value="Quirófano" {{ old('area_destino') == 'Quirófano' ? 'selected' : '' }}>Quirófano
                        </option>
                    </select>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow">
                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl shadow-md hover:bg-slate-800 transition">Guardar
                        Medicamento</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[500px]">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase border-b">
                            <tr>
                                <th class="px-6 py-4">Medicamento</th>
                                <th class="px-6 py-4 text-center">Stock</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($medicamentos as $med)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5">
                                    <p class="font-bold text-slate-800">{{ $med->nombre_medicamento }}</p>
                                    <p class="text-xs text-slate-400">Lote: {{ $med->codigo_lote }}</p>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex items-center justify-center h-10 w-10 rounded-xl font-bold {{ $med->cantidad_stock < 10 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $med->cantidad_stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="$store.modal.open('viewMed', {{ Js::from($med) }})"
                                            class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button @click="$store.modal.open('editMed', {{ Js::from($med) }})"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        {{-- Formulario oculto para eliminar --}}
                                        <form :id="'delete-form-{{ $med->id }}'"
                                            action="{{ route('medicamentos.destroy', $med->id) }}" method="POST"
                                            style="display: none;">
                                            @csrf @method('DELETE')
                                        </form>
                                        <button type="button"
                                            @click="confirmAction('Eliminar medicamento', '¿Seguro que quieres eliminar {{ $med->nombre_medicamento }}? Esta acción no se puede deshacer.', 'delete-form-{{ $med->id }}')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">No hay medicamentos
                                    registrados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Ver --}}
<x-modal id="viewMed" title="Detalles del Medicamento" maxWidth="max-w-lg">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-50 p-4 rounded-xl">
                <p class="text-xs font-bold text-slate-400 uppercase">Lote</p>
                <p class="font-semibold" x-text="$store.modal.data.codigo_lote"></p>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl">
                <p class="text-xs font-bold text-slate-400 uppercase">Área</p>
                <p class="font-semibold text-blue-600" x-text="$store.modal.data.area_destino"></p>
            </div>
        </div>
        <div class="bg-slate-50 p-4 rounded-xl">
            <p class="text-xs font-bold text-slate-400 uppercase">Nombre</p>
            <p class="font-bold text-lg" x-text="$store.modal.data.nombre_medicamento"></p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-50 p-4 rounded-xl">
                <p class="text-xs font-bold text-slate-400 uppercase">Vencimiento</p>
                <p x-text="$store.modal.data.fecha_vencimiento"></p>
            </div>
            <div class="bg-slate-50 p-4 rounded-xl">
                <p class="text-xs font-bold text-slate-400 uppercase">Stock</p>
                <p class="font-bold text-2xl" x-text="$store.modal.data.cantidad_stock"></p>
            </div>
        </div>
    </div>
    <div class="mt-6">
        <button @click="$store.modal.close()"
            class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl shadow">Cerrar</button>
    </div>
</x-modal>

{{-- Modal Editar --}}
<x-modal id="editMed" title="Editar Medicamento" maxWidth="max-w-md">
    <form :action="'/inventario/' + $store.modal.data.id" method="POST">
        @csrf
        @method('PUT')
        <input type="text" name="nombre_medicamento" x-model="$store.modal.data.nombre_medicamento"
            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow mb-4">
        <input type="number" name="cantidad_stock" x-model="$store.modal.data.cantidad_stock"
            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow mb-4">
        <select name="area_destino" x-model="$store.modal.data.area_destino"
            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 input-shadow mb-4">
            <option value="Urgencias">Urgencias</option>
            <option value="Pediatría">Pediatría</option>
            <option value="UCI">UCI</option>
            <option value="Quirófano">Quirófano</option>
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