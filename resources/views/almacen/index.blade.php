@extends('layouts.app')

@section('title', 'Gestión de Almacén')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{
    buscando: false,
    resultados: [],
    q: '',
    seleccionado: null,
    modalImportar: false, 
    modalMasivo: false,
    modalTrazabilidad: false,
    seleccionados: [],
    categoria: 'medicamento',
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
    },
    toggleTodos(event) {
        if (event.target.checked) {
            let ids = Array.from(document.querySelectorAll('.checkbox-item')).map(cb => cb.value);
            this.seleccionados = ids;
        } else {
            this.seleccionados = [];
        }
    }
}">
        {{-- Encabezado Adaptable a Celular --}}
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Gestión de Almacén</h1>
                <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Control de inventario - Hospital Dr. Tiburcio Garrido</p>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <button @click="modalTrazabilidad = true" 
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 text-blue-700 border border-blue-200 font-bold rounded-sm hover:bg-blue-100 transition shadow-sm text-xs uppercase tracking-wider">
                    <i class="fas fa-chart-line text-blue-600"></i> Monitoreo
                </button>
                <button @click="modalImportar = true" 
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-sm hover:bg-slate-50 transition shadow-sm text-xs uppercase tracking-wider">
                    <i class="fas fa-file-excel text-green-600"></i> Importar
                </button>
                <a href="{{ route('lotes.index') }}" 
                   class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white text-purple-700 border border-purple-200 font-bold rounded-sm hover:bg-purple-50 transition shadow-sm text-xs uppercase tracking-wider">
                    <i class="fas fa-boxes text-purple-600"></i> Lotes
                </a>
                <a :href="'{{ route('almacen.pdf') }}?categoria=' + categoria + '&tipo_insumo={{ request('tipo_insumo') }}'" 
                   target="_blank"
                   class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-2 bg-white border border-slate-200 text-slate-700 font-bold rounded-sm hover:bg-red-50 hover:text-red-700 transition shadow-sm text-xs uppercase tracking-wider">
                    <i class="fas fa-file-pdf text-red-600"></i> PDF
                </a>
            </div>
        </div>

        {{-- Formulario de Entrada Rápida --}}
        <div class="bg-white p-4 sm:p-6 rounded-sm border border-slate-100 shadow-sm mb-6 sm:mb-8">
            <h2 class="text-xs font-bold uppercase tracking-widest text-slate-700 mb-4 sm:mb-5 flex items-center gap-2">
                <span class="w-2 h-6 bg-slate-900 rounded-sm"></span> Registrar Entrada Rápida de Stock
            </h2>

            <form action="{{ route('stock.entrada') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end" x-on:submit="$store.loading.activate('Procesando entrada...')">
                @csrf
                <div class="relative md:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Insumos médicos / Medicamentos</label>
                    <input type="text" x-model="q" @input.debounce.300ms="buscar()" placeholder="Escriba para buscar..."
                           class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none transition font-semibold text-slate-700">
                    
                    <div class="absolute z-50 w-full mt-2 bg-white border border-slate-100 rounded-sm shadow-xl max-h-60 overflow-y-auto"
                         x-show="resultados.length > 0" x-cloak>
                        <template x-for="item in resultados" :key="item.id">
                            <button type="button" @click="seleccionar(item)"
                                    class="w-full text-left px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 font-bold transition border-b border-slate-50 last:border-0"
                                    x-text="item.text"></button>
                        </template>
                    </div>
                </div>

                <input type="hidden" name="medicamento_id" id="medicamento_id_real" required>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Cantidad a Sumar</label>
                    <input type="number" name="cantidad" required min="1" placeholder="Ej. 10"
                           class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 outline-none transition font-semibold text-slate-700">
                </div>

                <button type="submit" class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-sm hover:bg-slate-800 transition shadow-md flex items-center justify-center gap-2 text-xs uppercase tracking-wider">
                    <i class="fas fa-plus-circle"></i> Sumar Stock
                </button>
            </form>
        </div>

        {{-- BARRA SELECCIÓN MÚLTIPLE --}}
        <div x-show="seleccionados.length > 0" 
             x-transition 
             x-cloak
             class="mb-4 p-4 bg-blue-600 border border-blue-700 rounded-sm flex flex-col sm:flex-row items-center justify-between gap-3 shadow-md shadow-blue-500/10">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-white/20 rounded-sm flex items-center justify-center text-white shrink-0">
                    <i class="fas fa-check-double text-xs"></i>
                </div>
                <span class="text-xs font-bold text-white uppercase tracking-wider">
                    Seleccionados: <span class="bg-white text-blue-700 px-2 py-0.5 rounded-sm font-mono font-black" x-text="seleccionados.length"></span>
                </span>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="button" @click="seleccionados = []" 
                        class="text-xs font-bold text-blue-200 hover:text-white uppercase tracking-wider px-3 py-2 transition">
                    Desmarcar
                </button>
                <button type="button" @click="modalMasivo = true" 
                        class="bg-white hover:bg-slate-50 text-blue-700 text-xs font-extrabold px-4 py-2.5 rounded-sm uppercase tracking-wider transition shadow-sm">
                    <i class="fas fa-edit mr-1"></i> Editar Selección
                </button>
            </div>
        </div>

        {{-- BARRA DE FILTROS Y SELECTOR DE VISTA --}}
        <div class="bg-white p-4 rounded-sm border border-slate-100 shadow-sm mb-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <form action="{{ route('almacen.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                <div class="w-full sm:w-64">
                    <select name="tipo_insumo" onchange="this.form.submit()" class="w-full bg-slate-50 border-0 rounded-sm px-4 py-2.5 text-xs font-bold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                        <option value="">Todos los Tipos de Insumo</option>
                        @foreach($tiposInsumo as $tipo)
                            <option value="{{ $tipo }}" {{ request('tipo_insumo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>

                @if(request('tipo_insumo'))
                    <a href="{{ route('almacen.index') }}" class="text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-wider flex items-center justify-center gap-1">
                        <i class="fas fa-times-circle"></i> Limpiar Filtro
                    </a>
                @endif
            </form>

            {{-- TOGGLE ENTRE MEDICAMENTOS E INSUMOS MÉDICOS --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden md:inline">
                    Vista: <span class="text-slate-800 font-extrabold" x-text="categoria === 'medicamento' ? 'Medicamentos' : 'Insumos'"></span>
                </span>

                <div class="inline-flex rounded-sm p-1 bg-slate-100 border border-slate-200 w-full sm:w-auto">
                    <button type="button" 
                            @click="categoria = 'medicamento'"
                            :class="categoria === 'medicamento' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-500 hover:text-slate-700 font-semibold'"
                            class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 text-xs rounded-xs transition-all uppercase tracking-wider flex items-center justify-center gap-1.5">
                        <i class="fas fa-pills"></i> Medicamentos
                    </button>
                    <button type="button" 
                            @click="categoria = 'insumo'"
                            :class="categoria === 'insumo' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'text-slate-500 hover:text-slate-700 font-semibold'"
                            class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 text-xs rounded-xs transition-all uppercase tracking-wider flex items-center justify-center gap-1.5">
                        <i class="fas fa-syringes"></i> Insumos
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLA 1: MEDICAMENTOS --}}
        <div x-show="categoria === 'medicamento'" class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-pills text-blue-600"></i> Inventario de Medicamentos
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[650px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/30">
                            <th class="px-4 sm:px-6 py-4 text-center w-12">
                                <input type="checkbox" @change="toggleTodos($event)" class="rounded text-blue-600 focus:ring-blue-500/20 border-slate-300 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Nombre</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Tipo</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Lote / QR</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Mín.</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                        @forelse($inventario as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors" :class="seleccionados.includes('{{ $item->medicamento_id }}') ? 'bg-blue-50/40 hover:bg-blue-50/50' : ''">
                                <td class="px-4 sm:px-6 py-4 text-center">
                                    <input type="checkbox" 
                                           value="{{ $item->medicamento_id }}" 
                                           x-model="seleccionados"
                                           class="checkbox-item rounded text-blue-600 focus:ring-blue-500/20 border-slate-300 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="px-2 py-4 text-slate-900 font-bold tracking-tight">{{ $item->medicamento }}</td>
                                <td class="px-4 sm:px-6 py-4">
                                    <span class="px-2 py-1 rounded-sm text-[11px] font-bold tracking-wide
                                        {{ $item->tipo_insumo === 'Por Determinar' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                                        {{ $item->tipo_insumo ?? 'Por Determinar' }}
                                    </span>
                                </td>
                                
                                <td class="px-4 sm:px-6 py-2 text-center">
                                    @if(isset($item->codigo_lote) && $item->codigo_lote !== null && strlen(trim($item->codigo_lote)) > 0)
                                        @php
                                            $loteFormateado = trim($item->codigo_lote);
                                            $qrGenerado = false;
                                            $qrHtml = '';
                                            if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                                                try {
                                                    $qrHtml = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)
                                                        ->margin(1)
                                                        ->generate(route('almacen.lote', ['codigo_lote' => $loteFormateado]));
                                                    $qrGenerado = true;
                                                } catch (\Exception $e) {}
                                            }
                                        @endphp
                                        <div class="inline-flex flex-col items-center justify-center p-1.5 bg-white border border-slate-100 rounded-sm shadow-xs">
                                            @if($qrGenerado && !empty($qrHtml))
                                                <div class="p-1 bg-slate-50 rounded-sm border border-slate-100">
                                                    {!! $qrHtml !!}
                                                </div>
                                            @else
                                                <div class="p-1 bg-slate-50 rounded-sm border border-slate-100 flex items-center justify-center w-[50px] h-[50px] text-slate-400 text-[8px] uppercase font-bold">
                                                    <i class="fas fa-qrcode text-xl text-slate-300"></i>
                                                </div>
                                            @endif
                                            <a href="{{ route('almacen.lote', ['codigo_lote' => $loteFormateado]) }}" class="text-[10px] font-mono font-bold text-blue-600 hover:underline mt-1">
                                                {{ $loteFormateado }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic font-semibold">S/L</span>
                                    @endif
                                </td>

                                <td class="px-4 sm:px-6 py-4 text-center font-mono text-xs text-slate-400 font-bold">{{ $item->stock_minimo ?? '0' }}</td>
                                <td class="px-4 sm:px-6 py-4 text-center">
                                    <span class="px-3 py-1.5 rounded-sm text-xs font-bold font-mono {{ $item->stock_actual <= ($item->stock_minimo ?? 0) ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600' }}">
                                        {{ $item->stock_actual }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                    <i class="fas fa-box-open text-2xl block mb-2 text-slate-300 not-italic"></i> No se encontraron medicamentos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inventario->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-slate-50 bg-slate-50/30 overflow-x-auto">
                    {{ $inventario->links() }}
                </div>
            @endif
        </div>

        {{-- TABLA 2: INSUMOS MÉDICOS --}}
        <div x-show="categoria === 'insumo'" class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden" x-cloak>
            <div class="px-4 sm:px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-syringes text-blue-600"></i> Inventario de Insumos Médicos
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[650px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/30">
                            <th class="px-4 sm:px-6 py-4 text-center w-12">
                                <input type="checkbox" @change="toggleTodos($event)" class="rounded text-blue-600 focus:ring-blue-500/20 border-slate-300 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="px-2 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Nombre</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Tipo</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Lote / QR</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Mín.</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                        @forelse($insumos as $insumo)
                            <tr class="hover:bg-slate-50/50 transition-colors" :class="seleccionados.includes('{{ $insumo->id }}') ? 'bg-blue-50/40 hover:bg-blue-50/50' : ''">
                                <td class="px-4 sm:px-6 py-4 text-center">
                                    <input type="checkbox" 
                                           value="{{ $insumo->id }}" 
                                           x-model="seleccionados"
                                           class="checkbox-item rounded text-blue-600 focus:ring-blue-500/20 border-slate-300 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="px-2 py-4 text-slate-900 font-bold tracking-tight">{{ $insumo->nombre_insumo }}</td>
                                <td class="px-4 sm:px-6 py-4">
                                    <span class="px-2 py-1 rounded-sm text-[11px] font-bold tracking-wide
                                        {{ $insumo->tipo_insumo === 'Por Determinar' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                        {{ $insumo->tipo_insumo ?? 'Por Determinar' }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-2 text-center">
                                    @if(isset($insumo->codigo_lote) && $insumo->codigo_lote !== null && strlen(trim($insumo->codigo_lote)) > 0)
                                        @php
                                            $loteFormateado = trim($insumo->codigo_lote);
                                            $qrGenerado = false;
                                            $qrHtml = '';
                                            if (class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                                                try {
                                                    $qrHtml = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)
                                                        ->margin(1)
                                                        ->generate(route('almacen.lote', ['codigo_lote' => $loteFormateado]));
                                                    $qrGenerado = true;
                                                } catch (\Exception $e) {}
                                            }
                                        @endphp
                                        <div class="inline-flex flex-col items-center justify-center p-1.5 bg-white border border-slate-100 rounded-sm shadow-xs">
                                            @if($qrGenerado && !empty($qrHtml))
                                                <div class="p-1 bg-slate-50 rounded-sm border border-slate-100">
                                                    {!! $qrHtml !!}
                                                </div>
                                            @else
                                                <div class="p-1 bg-slate-50 rounded-sm border border-slate-100 flex items-center justify-center w-[50px] h-[50px] text-slate-400 text-[8px] uppercase font-bold">
                                                    <i class="fas fa-qrcode text-xl text-slate-300"></i>
                                                </div>
                                            @endif
                                            <a href="{{ route('almacen.lote', ['codigo_lote' => $loteFormateado]) }}" class="text-[10px] font-mono font-bold text-blue-600 hover:underline mt-1">
                                                {{ $loteFormateado }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic font-semibold">S/L</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center font-mono text-xs text-slate-400 font-bold">{{ $insumo->stock_minimo ?? '0' }}</td>
                                <td class="px-4 sm:px-6 py-4 text-center">
                                    <span class="px-3 py-1.5 rounded-sm text-xs font-bold font-mono {{ $insumo->cantidad_stock <= ($insumo->stock_minimo ?? 0) ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }}">
                                        {{ $insumo->cantidad_stock }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                    <i class="fas fa-box-open text-2xl block mb-2 text-slate-300 not-italic"></i> No se encontraron insumos médicos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($insumos, 'hasPages') && $insumos->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-slate-50 bg-slate-50/30 overflow-x-auto">
                    {{ $insumos->links() }}
                </div>
            @endif
        </div>

        {{-- MODAL DE EDICIÓN MASIVA --}}
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs flex items-center justify-center z-50 p-4" 
             x-show="modalMasivo" 
             x-cloak 
             x-transition>
             
            <div class="bg-white rounded-sm max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl max-h-[90vh] overflow-y-auto" 
                 @click.away="modalMasivo = false">
                 
                <div class="p-4 sm:p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                        <i class="fas fa-edit text-blue-600"></i> Edición Masiva
                    </h3>
                    <button type="button" @click="modalMasivo = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('almacen.editar-masivo') }}" method="POST"
                      x-on:submit="$store.loading.activate('Aplicando cambios masivos...')">
                    @csrf
                    <input type="hidden" name="ids" :value="JSON.stringify(seleccionados)">
                    <input type="hidden" name="tabla" :value="categoria === 'insumo' ? 'insumos' : 'medicamentos'">

                    <div class="p-4 sm:p-6 space-y-4">
                        <div class="bg-slate-50 p-3 rounded-sm border border-slate-100">
                            <p class="text-[11px] text-slate-500 uppercase font-semibold">
                                Afectará a <span class="text-blue-600 font-bold" x-text="seleccionados.length"></span> elementos en <span class="font-bold text-slate-700" x-text="categoria === 'insumo' ? 'Insumos Médicos' : 'Medicamentos'"></span>.
                            </p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Nuevo Código de Lote</label>
                            <input type="text" name="codigo_lote" placeholder="Ej: LOTE-2026"
                                   class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Nuevo Stock Disponible</label>
                            <input type="number" name="cantidad_stock" min="0" placeholder="Ej: 150"
                                   class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 transition">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Fecha de Vencimiento</label>
                            <input type="date" name="fecha_vencimiento" 
                                   class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 transition">
                            <span class="text-[10px] text-slate-500 mt-1 block">Déjalo vacío si no deseas modificarla.</span>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                        <button type="button" @click="modalMasivo = false"
                            class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-3 rounded-sm hover:bg-slate-100 transition text-xs uppercase tracking-wider">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-sm hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition text-xs uppercase tracking-wider">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DE IMPORTACIÓN --}}
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs flex items-center justify-center z-50 p-4" 
             x-show="modalImportar" 
             x-cloak 
             x-transition
             x-data="{ tipoImportacion: 'insumos' }">
             
            <div class="bg-white rounded-sm max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl" 
                 @click.away="modalImportar = false">
                 
                <div class="p-4 sm:p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                        <i class="fas fa-file-excel text-green-600"></i> Importar Masivo
                    </h3>
                    <button type="button" @click="modalImportar = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form :action="tipoImportacion === 'insumos' ? '{{ route('insumos.import') }}' : '{{ route('inventario.import') }}'" method="POST" enctype="multipart/form-data"
                      x-on:submit="$store.loading.activate('Importando y clasificando datos...')">
                    @csrf
                    <div class="p-4 sm:p-6 space-y-4">
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipo de Registro</label>
                                <a :href="tipoImportacion === 'insumos' ? '{{ asset('plantillas/almacen_insumos_medicos.xlsx') }}' : '{{ asset('plantillas/almacen_medicamentos.xlsx') }}'" 
                                   download
                                   class="text-[10px] font-bold text-blue-600 hover:text-blue-800 uppercase tracking-wider flex items-center gap-1 transition">
                                    <i class="fas fa-download"></i> Plantilla
                                </a>
                            </div>

                            <select class="w-full bg-slate-50 border-0 rounded-sm px-3 py-2.5 text-xs font-bold text-slate-700 outline-none"
                                    x-model="tipoImportacion">
                                <option value="insumos">Insumos Médicos</option>
                                <option value="medicamentos">Medicamentos</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-widest">Archivo Excel / CSV</label>
                            <input type="file" name="archivo" accept=".xlsx, .xls, .csv" required
                                class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-sm file:border-0 file:bg-blue-50 file:text-blue-700 file:font-bold">
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
                        <button type="button" @click="modalImportar = false"
                            class="flex-1 bg-white border border-slate-200 text-slate-600 font-bold py-3 rounded-sm hover:bg-slate-100 transition text-xs uppercase tracking-wider">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-sm hover:bg-blue-700 shadow-lg shadow-blue-500/20 transition text-xs uppercase tracking-wider">
                            Cargar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DE TRAZABILIDAD Y CONSUMO --}}
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-xs flex items-center justify-center z-50 p-4" 
             x-show="modalTrazabilidad" 
             x-cloak 
             x-transition>
             
            <div class="bg-white rounded-sm max-w-2xl w-full border border-slate-100 overflow-hidden shadow-2xl max-h-[90vh] flex flex-col" 
                 @click.away="modalTrazabilidad = false">
                 
                <div class="p-4 sm:p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50 shrink-0">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-600"></i> Monitoreo de Consumo
                    </h3>
                    <button type="button" @click="modalTrazabilidad = false" class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-y-auto flex-1">
                    <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-500 text-white rounded-sm flex items-center justify-center font-bold shrink-0">
                                <i class="fas fa-boxes-stacked text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">Almacenado / Entrada Hoy</p>
                                <p class="text-xs text-emerald-600 font-semibold">Unidades agregadas el día de hoy</p>
                            </div>
                        </div>
                        <span class="text-xl sm:text-2xl font-black font-mono text-emerald-700 self-end sm:self-auto">
                            {{ number_format($almacenadoHoy) }} <span class="text-xs font-normal">unds</span>
                        </span>
                    </div>

                    <form action="{{ route('almacen.index') }}" method="GET" class="bg-slate-50 p-4 rounded-sm border border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 mb-3 uppercase tracking-wider">Filtrar Consumo por Rango de Fechas</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Desde</label>
                                <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}" 
                                       class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 uppercase mb-1">Hasta</label>
                                <input type="date" name="fecha_fin" value="{{ $fechaFin }}" 
                                       class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                            </div>
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-sm hover:bg-blue-700 transition text-xs uppercase tracking-wider">
                                <i class="fas fa-search mr-1"></i> Consultar
                            </button>
                        </div>
                    </form>

                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3">
                            Consumo del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                        </h4>

                        <div class="max-h-48 overflow-y-auto border border-slate-100 rounded-sm">
                            <table class="w-full text-left border-collapse">
                                <thead class="sticky top-0 bg-slate-50 border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Insumo / Medicamento</th>
                                        <th class="px-4 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-right">Consumido</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 text-xs font-medium text-slate-600">
                                    @forelse($consumoPorFecha as $row)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-2.5 font-bold text-slate-800">{{ $row->medicamento }}</td>
                                            <td class="px-4 py-2.5 text-right font-mono font-bold text-red-600">
                                                -{{ number_format($row->total_consumido) }} unds
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-center text-xs text-slate-400 italic">
                                                No hay registros de consumo en este rango.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 text-right shrink-0">
                    <button type="button" @click="modalTrazabilidad = false"
                            class="bg-white border border-slate-200 text-slate-600 font-bold px-5 py-2 rounded-sm hover:bg-slate-100 transition text-xs uppercase tracking-wider">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection