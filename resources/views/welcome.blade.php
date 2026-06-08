@extends('layouts.app')

@section('title', 'Inventario de Medicamentos')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Módulo de Inventario</h1>
        <p class="text-slate-500 mt-1">Hospital Dr. Tiburcio Garrido</p>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-xl shadow-sm">
        <ul class="list-disc pl-5 text-sm">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" 
         x-data="{ 
            editOpen: false, 
            medicamento: { id: '', nombre_medicamento: '', cantidad_stock: '', area_destino: '', fecha_vencimiento: '' },
            abrirEditar(item) {
                this.medicamento = { ...item };
                this.editOpen = true;
            }
         }">
         
      
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm sticky top-6">
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Nuevo Registro
                </h2>
                
                <form action="{{ route('medicamentos.store') }}" method="POST" class="space-y-4"
                    x-on:submit.prevent="$store.loading.submitForm($event.target)">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Nombre del Medicamento</label>
                        <input type="text" name="nombre_medicamento" value="{{ old('nombre_medicamento') }}"
                            placeholder="Ej: Omeprazol 40mg" required
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Código de Lote</label>
                        <input type="text" name="codigo_lote" placeholder="Ej: LOTE-A12"
                        class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Cantidad en Stock</label>
                        <input type="number" name="cantidad_stock" value="{{ old('cantidad_stock') }}"
                            placeholder="0" required
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Área de Destino</label>
                        <select name="area_destino" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                            <option value="" disabled selected>Seleccionar área...</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->nombre_area }}" {{ old('area_destino') == $area->nombre_area ? 'selected' : '' }}>
                                    {{ $area->nombre_area }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition-all transform hover:-translate-y-1">
                        Guardar en Inventario
                    </button>
                </form>
            </div>
        </div>

        
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Medicamento</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Stock</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Área</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($medicamentos as $med)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-700 block text-sm">{{ $med->nombre_medicamento }}</span>
                                <span class="text-[10px] text-slate-400 font-medium uppercase italic block">Vence: {{ \Carbon\Carbon::parse($med->fecha_vencimiento)->format('d/m/Y') }}</span>
                                <span class="text-[9px] font-mono text-slate-400">Lote: {{ $med->codigo_lote ?? 'S/L' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-lg text-xs font-black {{ $med->cantidad_stock <= 5 ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                    {{ $med->cantidad_stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ $med->area_destino }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    {{-- 🟢 BOTÓN EDITAR CORREGIDO (JSON encode evita errores de sintaxis) --}}
                                    <button type="button" 
                                            @click='abrirEditar({{ json_encode($med->only(['id','nombre_medicamento','cantidad_stock','area_destino','fecha_vencimiento'])) }})' 
                                            class="p-2 text-slate-400 hover:text-blue-600 transition"
                                            title="Editar Registro">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <form action="{{ route('medicamentos.destroy', $med->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition" onclick="return confirm('¿Eliminar registro?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        
        <div x-show="editOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             x-transition>
            
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-100 max-w-md w-full"
                 @click.away="editOpen = false">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-blue-600"></i> Editar Medicamento
                    </h3>
                    <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- El action se genera dinámicamente apuntando a la ruta index base con barra final --}}
                <form :action="'{{ route('medicamentos.index') }}/' + medicamento.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Nombre del Medicamento</label>
                        <input type="text" name="nombre_medicamento" x-model="medicamento.nombre_medicamento" required
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm font-semibold">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Stock Actual</label>
                            <input type="number" name="cantidad_stock" x-model="medicamento.cantidad_stock" required
                                class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Área Destino</label>
                            <select name="area_destino" x-model="medicamento.area_destino" required
                                class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                                @foreach($areas as $area)
                                    <option value="{{ $area->nombre_area }}">{{ $area->nombre_area }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" x-model="medicamento.fecha_vencimiento" required
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="button" @click="editOpen = false"
                            class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-xl hover:bg-slate-200 transition text-sm">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition text-sm">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection