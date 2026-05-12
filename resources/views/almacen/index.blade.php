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

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Columna Izquierda: Formulario de Entrada Rápida --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600"></i> Entrada Rápida
                </h2>
                
                {{-- RUTA CORREGIDA: Según tu web.php es stock.entrada --}}
                <form action="{{ route('stock.entrada') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Medicamento</label>
        <select name="medicamento_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="" disabled selected>Seleccionar...</option>
            @foreach($todosLosMedicamentos as $med)
                <option value="{{ $med->id }}">{{ $med->nombre_medicamento }}</option>
            @endforeach
        </select>
    </div>

    {{-- NUEVO: Select de Área para validar --}}
    <div>
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Área Destino</label>
        <select name="area_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="" disabled selected>Seleccionar área...</option>
            @foreach($areas as $area)
                <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Cantidad a Sumar</label>
        <input type="number" name="cantidad" min="1" required placeholder="Ej: 50"
               class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 transition-all">
    </div>

    <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 rounded-xl hover:bg-slate-800 transition shadow-lg">
        Actualizar Stock
    </button>
</form>
            </div>

        </div>

        {{-- Columna Derecha: Tabla de Inventario --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h2 class="font-bold text-slate-800">Estado Actual del Inventario</h2>
                    
                    <form action="{{ route('almacen.index') }}" method="GET" class="flex gap-2">
                        <select name="area_id" onchange="this.form.submit()" class="text-sm border-slate-200 rounded-lg focus:ring-blue-500">
                            <option value="">Todas las áreas</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                    {{ $area->nombre_area }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4 font-semibold">Medicamento</th>
                                <th class="px-6 py-4 font-semibold">Presentación</th>
                                <th class="px-6 py-4 font-semibold">Área Destino</th>
                                <th class="px-6 py-4 font-semibold">Stock</th>
                                <th class="px-6 py-4 font-semibold text-right text-transparent">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($inventario as $item)
                                <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition group">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900">{{ $item->medicamento }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 text-sm italic">
                                        {{ $item->presentacion ?? 'Sin especificar' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-tighter">
                                            {{ $item->area_destino }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg font-black {{ $item->stock_actual <= $item->stock_minimo ? 'text-red-600' : 'text-slate-900' }}">
                                                {{ $item->stock_actual }}
                                            </span>
                                            @if($item->stock_actual <= $item->stock_minimo)
                                                <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="opacity-0 group-hover:opacity-100 transition-opacity text-slate-400 hover:text-blue-600">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                        <i class="fas fa-box-open text-4xl mb-3 block"></i>
                                        No se encontraron registros.
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

{{-- Modal Importar --}}
<x-modal id="modalImportar" title="Importar Inventario" maxWidth="max-w-md">
    <form action="{{ route('inventario.import') }}" method="POST" enctype="multipart/form-data" 
          x-on:submit="$store.loading.activate('Importando datos...')">
        @csrf
        <div class="space-y-4">
            <label class="block text-sm font-medium text-slate-700">Archivo Excel (F15)</label>
            <input type="file" name="archivo" accept=".xlsx, .xls, .csv" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700">
            
            <label class="block text-sm font-medium text-slate-700">Asignar a Área</label>
            <select name="area_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 outline-none">
                <option value="" disabled selected>Seleccione área destino</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-3 mt-8">
            <button type="button" @click="$store.modal.close()" class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-xl hover:bg-slate-200 transition">
                Cancelar
            </button>
            <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition">
                Comenzar Carga
            </button>
        </div>
    </form>
</x-modal>

@endsection