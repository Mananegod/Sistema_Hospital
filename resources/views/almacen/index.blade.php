@extends('layouts.app')

@section('title', 'Gestión de Almacén')

@section('content')
{{-- Componentes de Notificación y Carga --}}
@include('components.mensajes-notificaciones')
@include('components.loading-overlay')

<div class="max-w-7xl mx-auto" x-data>
    {{-- Encabezado --}}
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Gestión de Almacén</h1>
            <p class="text-slate-500 mt-1">Control de inventario - Hospital Dr. Tiburcio Garrido</p>
        </div>
        <button @click="$store.modal.open('modalImportar')" class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">
            <i class="fas fa-file-excel text-green-600"></i> Importar Excel
        </button>
    </div>

    {{-- Disparadores de Notificaciones --}}
    @if(session('success'))
        <div x-init="$nextTick(() => $store.toast.add('{{ session('success') }}', 'success'))"></div>
    @endif
    @if(session('error'))
        <div x-init="$nextTick(() => $store.toast.add('{{ session('error') }}', 'error'))"></div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Panel de Ajuste Rápido --}}
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm sticky top-6">
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Ajuste Rápido
                </h2>
                
                <form action="{{ route('stock.entrada') }}" method="POST" class="space-y-4" 
                      x-on:submit="$store.loading.activate('Procesando ajuste...')">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Insumo / Medicamento</label>
                        <select name="medicamento_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="" disabled selected>Seleccione...</option>
                            @foreach($todosLosMedicamentos as $med)
                                <option value="{{ $med->id }}">{{ $med->nombre }} - {{ $med->presentacion }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Área de Destino</label>
                        <select name="area_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="" disabled selected>Seleccione área...</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Cantidad</label>
                        <input type="number" name="cantidad" min="1" placeholder="Ej: 10" required 
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-md hover:bg-slate-800 transition">
                        Actualizar Stock
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabla de Inventario --}}
        <div class="lg:col-span-8">
            <div class="mb-4">
                <form action="{{ route('almacen.index') }}" method="GET" class="flex gap-2">
                    <select name="area_id" onchange="this.form.submit()" class="flex-1 bg-white border border-slate-200 rounded-xl px-4 py-2 outline-none text-sm shadow-sm">
                        <option value="">Todas las áreas</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->nombre_area }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase border-b">
                        <tr>
                            <th class="px-6 py-4">Insumo</th>
                            <th class="px-6 py-4">Ubicación</th>
                            <th class="px-6 py-4 text-center">Stock</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($inventario as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-5">
                                <p class="font-bold text-slate-800">{{ $item->medicamento }}</p>
                                <p class="text-[10px] text-blue-600 font-extrabold uppercase">{{ $item->presentacion }}</p>
                            </td>
                            <td class="px-6 py-5 text-slate-600 text-sm italic">{{ $item->area }}</td>
                            <td class="px-6 py-5 text-center font-extrabold {{ $item->stock_actual <= $item->stock_minimo ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $item->stock_actual }}
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase {{ $item->stock_actual <= $item->stock_minimo ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $item->stock_actual <= $item->stock_minimo ? 'Crítico' : 'Estable' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">No hay registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Importar --}}
<x-modal id="modalImportar" title="Importar Inventario" maxWidth="max-w-md">
    <form action="{{ route('inventario.import') }}" method="POST" enctype="multipart/form-data" 
          x-on:submit="$store.loading.activate('Importando datos...')">
        @csrf
        <div class="space-y-4">
            <input type="file" name="archivo" accept=".xlsx, .xls, .csv" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700">
            <select name="area_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 outline-none">
                <option value="" disabled selected>Seleccione área destino</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3 mt-8">
            <button type="button" @click="$store.modal.close()" class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-xl">Cancelar</button>
            <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-700 transition">Importar</button>
        </div>
    </form>
</x-modal>
@endsection