{{--
   Vista optimizada de gestión de almacén:
   - Tabla con paginación
   - Select de medicamento con búsqueda asíncrona
   - Formulario de Entrada Rápida vinculado a 'stock.entrada'
   - Modal de Importación F15 con lógica local aislada (Estilo Pacientes)
--}}
@extends('layouts.app')

@section('title', 'Gestión de Almacén')

@section('content')
    @include('components.mensajes-notificaciones')
    @include('components.loading-overlay')

    {{-- Inicializamos 'modalImportar: false' en el x-data local para blindarlo offline --}}
    <div class="max-w-7xl mx-auto" x-data="{
        buscando: false,
        resultados: [],
        q: '',
        seleccionado: null,
        modalImportar: false, 
        async buscar() {
            if (this.q.length < 2) { this.resultados = []; return; }
            this.buscando = true;
            try {
                let res = await fetch('{{ route('medicamentos.buscar') }}?q=' + encodeURIComponent(this.q));
                this.resultados = await res.json();
            } catch(e) { this.resultados = []; }
            this.buscando = false;
        },
        seleccionar(item) {
            this.seleccionado = item;
            this.q = item.text;
            this.resultados = [];
            document.getElementById('medicamento_id_real').value = item.id;
        }
    }">
        {{-- Encabezado --}}
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Gestión de Almacén</h1>
                <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Control de inventario - Hospital Dr. Tiburcio Garrido</p>
            </div>
            
            <div class="flex gap-3">
                {{-- Botón con acción local para abrir el modal --}}
                <button @click="modalImportar = true" 
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-50 transition shadow-sm">
                    <i class="fas fa-file-excel text-green-600"></i> Importar Excel
                </button>
            </div>
        </div>

        {{-- Formulario de Entrada Rápida (Acoplado de forma exacta a tu ruta) --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-8">
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-700 mb-5 flex items-center gap-2">
                <span class="w-2 h-6 bg-slate-900 rounded-full"></span> Registrar Entrada Rápida de Stock
            </h2>

            <form action="{{ route('stock.entrada') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end" x-on:submit="$store.loading.activate('Procesando entrada...')">
                @csrf
                {{-- Input buscador asíncrono --}}
                <div class="relative">
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Medicamento</label>
                    <input type="text" x-model="q" @input.debounce.300ms="buscar()" placeholder="Escriba para buscar..."
                           class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none transition font-semibold text-slate-700">
                    
                    {{-- Resultados de la búsqueda --}}
                    <div class="absolute z-50 w-full mt-2 bg-white border border-slate-100 rounded-xl shadow-xl max-h-60 overflow-y-auto"
                         x-show="resultados.length > 0" x-cloak>
                        <template x-for="item in resultados" :key="item.id">
                            <button type="button" @click="seleccionar(item)"
                                    class="w-full text-left px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 font-bold transition border-b border-slate-50 last:border-0"
                                    x-text="item.text"></button>
                        </template>
                    </div>
                </div>

                {{-- Input ID oculto que se llena al seleccionar --}}
                <input type="hidden" name="medicamento_id" id="medicamento_id_real" required>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Área de Ubicación</label>
                    <select name="area_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 text-sm text-slate-600 font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        <option value="" disabled selected>Seleccione el área...</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Cantidad a Sumar</label>
                    <input type="number" name="cantidad" required min="1" placeholder="Ej. 10"
                           class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none transition font-semibold text-slate-700">
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-xl hover:bg-slate-800 transition shadow-md flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                    <i class="fas fa-plus-circle"></i> Sumar Stock
                </button>
            </form>
        </div>

        {{-- Tabla de Inventario Actual --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Existencias en Almacén</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/30">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Medicamento</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Presentación</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Stock Mínimo</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Stock Actual</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Área de Ubicación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                        @forelse($inventario as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-slate-900 font-bold tracking-tight">{{ $item->medicamento }}</td>
                                <td class="px-6 py-4 text-slate-500 text-xs font-semibold">{{ $item->presentacion ?? 'N/C' }}</td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-slate-400 font-bold">{{ $item->stock_minimo ?? '0' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold font-mono {{ $item->stock_actual <= ($item->stock_minimo ?? 0) ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600' }}">
                                        {{ $item->stock_actual }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wide bg-slate-100 text-slate-600">
                                        {{ $item->area_destino }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                    <i class="fas fa-box-open text-2xl block mb-2 text-slate-300 not-italic"></i> No se encontraron registros en el almacén central.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($inventario->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                    {{ $inventario->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL DE IMPORTACIÓN INTERNO (Estilo Seguro Offline de Pacientes) --}}
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs flex items-center justify-center z-50 p-4" 
             x-show="modalImportar" 
             x-cloak 
             x-transition>
             
            <div class="bg-white rounded-3xl max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl" 
                 @click.away="modalImportar = false">
                 
                {{-- Encabezado --}}
                <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                        <i class="fas fa-file-excel text-green-600"></i> Importar Inventario
                    </h3>
                    <button @click="modalImportar = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Formulario Interno --}}
                <form action="{{ route('inventario.import') }}" method="POST" enctype="multipart/form-data"
                      x-on:submit="$store.loading.activate('Importando datos...')">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Archivo Excel (F15)</label>
                            <input type="file" name="archivo" accept=".xlsx, .xls, .csv" required
                                class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold file:text-xs">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Asignar a Área</label>
                            <select name="area_id" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 outline-none text-sm font-semibold text-slate-700">
                                <option value="" disabled selected>Seleccione área destino</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Acciones del Modal --}}
                    <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                        <button type="button" @click="modalImportar = false"
                            class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-3 rounded-xl hover:bg-slate-100 transition text-xs uppercase tracking-wider">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition text-xs uppercase tracking-wider">
                            Comenzar Carga
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection