@extends('layouts.app')

@section('title', 'Inventario de Medicamentos')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Módulo de Inventario</h1>
        <p class="text-slate-500 mt-1">Hospital Dr. Tiburcio Garrido</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" 
         x-data="{ 
            editOpen: false, 
            medicamento: { id: '', nombre_medicamento: '', cantidad_stock: '', fecha_vencimiento: '' },
            abrirEditar(item) {
                this.medicamento = { ...item };
                this.editOpen = true;
            }
         }">
         
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm sticky top-6">
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
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Código de Lote</label>
                        <input type="text" name="codigo_lote" placeholder="Ej: LOTE-A12"
                        class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Cantidad en Stock</label>
                        <input type="number" name="cantidad_stock" value="{{ old('cantidad_stock') }}"
                            placeholder="0" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-4 rounded-sm shadow-lg hover:bg-slate-800 transition-all transform hover:-translate-y-1">
                        Guardar en Inventario
                    </button>
                </form>
            </div>
        </div>

        {{-- Panel Derecho: Tabla con scroll horizontal --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Medicamento</th>
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Stock</th>
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Ubicación</th>
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($medicamentos as $med)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-700 block text-sm whitespace-nowrap">{{ $med->nombre_medicamento }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium uppercase italic block whitespace-nowrap">Vence: {{ \Carbon\Carbon::parse($med->fecha_vencimiento)->format('d/m/Y') }}</span>
                                    <span class="text-[9px] font-mono text-slate-400 block whitespace-nowrap">Lote: {{ $med->codigo_lote ?? 'S/L' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-sm text-xs font-black inline-block whitespace-nowrap {{ $med->cantidad_stock <= 5 ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                        {{ $med->cantidad_stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-500 uppercase whitespace-nowrap">{{ $med->area_destino }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 whitespace-nowrap">
                                        <button type="button" 
                                                @click='abrirEditar({{ json_encode($med->only(['id','nombre_medicamento','cantidad_stock','fecha_vencimiento'])) }})' 
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
        </div>

        {{-- Modal para Confirmar Incremento de Stock por Duplicado --}}
        @if(session('duplicado_detectado'))
        @php $dup = session('duplicado_detectado'); @endphp
        <div x-data="{ duplicateOpen: true }" 
             x-show="duplicateOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             x-transition>
            
            <div class="bg-white p-6 rounded-sm shadow-xl border border-slate-100 max-w-md w-full"
                 @click.away="duplicateOpen = false">
                
                <div class="flex items-center gap-3 mb-4 text-amber-500">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    <h3 class="text-lg font-bold text-slate-900">Medicamento Duplicado</h3>
                </div>

                <p class="text-slate-600 text-sm mb-6">
                    El medicamento <strong class="text-slate-800">{{ $dup['nombre_medicamento'] }}</strong> ya se encuentra registrado en el almacén. ¿Quieres aumentar el stock?
                </p>

                <form action="{{ route('medicamentos.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="confirmar_incremento" value="1">
                    <input type="hidden" name="nombre_medicamento" value="{{ $dup['nombre_medicamento'] }}">
                    <input type="hidden" name="cantidad_stock" value="{{ $dup['cantidad_stock'] }}">
                    <input type="hidden" name="fecha_vencimiento" value="{{ $dup['fecha_vencimiento'] }}">
                    <input type="hidden" name="codigo_lote" value="{{ $dup['codigo_lote'] }}">

                    <div class="flex gap-3">
                        <button type="button" @click="duplicateOpen = false"
                            class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-sm hover:bg-slate-200 transition text-sm">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-sm hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition text-sm">
                            Aumentar Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Modal Editar --}}
        <div x-show="editOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             x-transition>
            
            <div class="bg-white p-6 rounded-sm shadow-xl border border-slate-100 max-w-md w-full"
                 @click.away="editOpen = false">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-pen-to-square text-blue-600"></i> Editar Medicamento
                    </h3>
                    <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ route('medicamentos.index') }}/' + medicamento.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Nombre del Medicamento</label>
                        <input type="text" name="nombre_medicamento" x-model="medicamento.nombre_medicamento" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Stock Actual</label>
                        <input type="number" name="cantidad_stock" x-model="medicamento.cantidad_stock" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" x-model="medicamento.fecha_vencimiento" required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                    </div>

                    <div class="flex gap-3 mt-8">
                        <button type="button" @click="editOpen = false"
                            class="flex-1 bg-slate-100 text-slate-700 font-bold py-3 rounded-sm hover:bg-slate-200 transition text-sm">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-sm hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition text-sm">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection