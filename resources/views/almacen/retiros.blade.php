@extends('layouts.app')

@section('title', 'Retiro de Medicamentos e Insumos')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="{
        tipoForm: 'medicamento',
        modalStock: false,
        modalStockData: { nombre: '', disponible: 0, solicitado: 0 },
        abrirModalStock(nombre, disponible, solicitado) {
            this.modalStockData = { nombre, disponible, solicitado };
            this.modalStock = true;
        },
        cerrarModalStock() {
            this.modalStock = false;
        },
        retiros: [
            @foreach($ultimosRetiros as $retiro)
                @php
                    $r = is_array($retiro) ? (object) $retiro : $retiro;
                    $nombre = $r->nombre ?? $r->nombre_medicamento ?? $r->nombre_insumo ?? '';
                    $tipoExplicit = strtolower((string)($r->tipo_item ?? $r->tipo ?? ''));

                    if ($tipoExplicit === 'insumo' || !empty($r->insumo_id) || !empty($r->nombre_insumo)) {
                        $tipoTexto = 'insumo';
                    } elseif ($tipoExplicit === 'medicamento' || !empty($r->medicamento_id) || !empty($r->nombre_medicamento)) {
                        $tipoTexto = 'medicamento';
                    } else {
                        $esInsumo = false;
                        if (isset($todosLosInsumos) && !empty($nombre)) {
                            foreach ($todosLosInsumos as $ins) {
                                $insNombre = is_array($ins) ? ($ins['nombre_insumo'] ?? '') : ($ins->nombre_insumo ?? '');
                                if (strcasecmp(trim($insNombre), trim($nombre)) === 0) {
                                    $esInsumo = true;
                                    break;
                                }
                            }
                        }
                        $tipoTexto = $esInsumo ? 'insumo' : 'medicamento';
                    }
                @endphp
                {
                    id: {{ $r->id ?? 0 }},
                    nombre: '{{ addslashes($nombre) }}',
                    nombre_area: '{{ addslashes($r->nombre_area ?? '') }}',
                    cantidad: {{ $r->cantidad ?? 0 }},
                    created_at: '{{ \Carbon\Carbon::parse($r->created_at ?? now())->format('d/m/Y H:i') }}',
                    tipo: '{{ $tipoTexto }}'
                },
            @endforeach
        ],
        medicamentos: [
            @foreach($todosLosMedicamentos as $med)
                { id: {{ $med->id }}, nombre: '{{ addslashes($med->nombre_medicamento) }}', stock: {{ $med->cantidad_stock ?? 0 }} },
            @endforeach
        ],
        insumos: [
            @foreach($todosLosInsumos as $ins)
                { id: {{ $ins->id }}, nombre: '{{ addslashes($ins->nombre_insumo) }}', stock: {{ $ins->cantidad_stock ?? 0 }} },
            @endforeach
        ],
        areas: [
            @foreach($areas as $area)
                { id: {{ $area->id }}, nombre_area: '{{ addslashes($area->nombre_area) }}' },
            @endforeach
        ],
        formMed: {
            medicamento_id: '',
            area_id: '',
            cantidad: ''
        },
        formIns: {
            insumo_id: '',
            area_id: '',
            cantidad: ''
        },
        stockMed() {
            let m = this.medicamentos.find(i => i.id == this.formMed.medicamento_id);
            return m ? m.stock : null;
        },
        stockIns() {
            let i = this.insumos.find(item => item.id == this.formIns.insumo_id);
            return i ? i.stock : null;
        },
        registrarRetiroMed() {
            if (!this.formMed.medicamento_id || !this.formMed.area_id || !this.formMed.cantidad) {
                if (this.$store && this.$store.toast) this.$store.toast.add('Por favor llena todos los campos.', 'error');
                return;
            }
            let s = this.stockMed();
            let cantidad = parseInt(this.formMed.cantidad);
            if (s !== null && cantidad > s) {
                let med = this.medicamentos.find(i => i.id == this.formMed.medicamento_id);
                this.abrirModalStock(med ? med.nombre : 'Medicamento', s, cantidad);
                return;
            }
            this.enviarForm(this.formMed, 'medicamento');
        },
        registrarRetiroIns() {
            if (!this.formIns.insumo_id || !this.formIns.area_id || !this.formIns.cantidad) {
                if (this.$store && this.$store.toast) this.$store.toast.add('Por favor llena todos los campos.', 'error');
                return;
            }
            let s = this.stockIns();
            let cantidad = parseInt(this.formIns.cantidad);
            if (s !== null && cantidad > s) {
                let ins = this.insumos.find(i => i.id == this.formIns.insumo_id);
                this.abrirModalStock(ins ? ins.nombre : 'Insumo', s, cantidad);
                return;
            }
            this.enviarForm(this.formIns, 'insumo');
        },
        enviarForm(data, tipo) {
            if (this.$store && this.$store.loading) {
                this.$store.loading.activate('Procesando retiro...');
            }
            let formSubmit = document.createElement('form');
            formSubmit.method = 'POST';
            formSubmit.action = '{{ route('almacen.retiros.store') }}';

            let csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            formSubmit.appendChild(csrfInput);

            let typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'tipo_item';
            typeInput.value = tipo;
            formSubmit.appendChild(typeInput);

            let tipoInput = document.createElement('input');
            tipoInput.type = 'hidden';
            tipoInput.name = 'tipo';
            tipoInput.value = tipo;
            formSubmit.appendChild(tipoInput);

            for (let key in data) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                formSubmit.appendChild(input);
            }

            document.body.appendChild(formSubmit);
            formSubmit.submit();
        }
    }">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Retiro de Insumos</h1>
                <p class="text-slate-500 mt-1">Registre la salida de medicamentos e insumos médicos de las áreas correspondientes</p>
            </div>
            <div>
                <a href="{{ route('almacen.retiros.pdf') }}" target="_blank"
                    class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-4 rounded-sm shadow-md transition duration-200 text-sm">
                    <i class="fas fa-file-pdf text-red-400"></i> Exportar PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Formularios de Retiro --}}
            <div class="lg:col-span-4 space-y-4 sticky top-6">

                {{-- Botones de Selección de Formulario --}}
                <div class="bg-white p-1 rounded-sm border border-slate-100 shadow-sm flex gap-1">
                    <button type="button" 
                        @click="tipoForm = 'medicamento'"
                        :class="tipoForm === 'medicamento' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                        class="flex-1 py-2 px-3 text-xs rounded-xs transition text-center flex items-center justify-center gap-2">
                        <i class="fas fa-pills"></i> Medicamentos
                    </button>
                    <button type="button" 
                        @click="tipoForm = 'insumo'"
                        :class="tipoForm === 'insumo' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-50 font-medium'"
                        class="flex-1 py-2 px-3 text-xs rounded-xs transition text-center flex items-center justify-center gap-2">
                        <i class="fas fa-box-tissue"></i> Insumos
                    </button>
                </div>

                {{-- FORMULARIO 1: MEDICAMENTOS --}}
                <div x-show="tipoForm === 'medicamento'" class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm">
                    <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                        <i class="fas fa-minus-circle text-red-500"></i> Retirar Medicamento
                    </h2>
                    <form @submit.prevent="registrarRetiroMed()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Medicamento</label>
                                <select x-model="formMed.medicamento_id"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Seleccione medicamento</option>
                                    <template x-for="med in medicamentos" :key="med.id">
                                        <option :value="med.id" x-text="med.nombre"></option>
                                    </template>
                                </select>
                                <template x-if="stockMed() !== null">
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        Stock disponible: <span class="text-blue-600 font-bold" x-text="stockMed()"></span>
                                    </p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Área de Retiro</label>
                                <select x-model="formMed.area_id"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Seleccione área</option>
                                    <template x-for="area in areas" :key="area.id">
                                        <option :value="area.id" x-text="area.nombre_area"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Cantidad a Retirar</label>
                                <input type="number" x-model="formMed.cantidad" min="1" placeholder="Ej. 10"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit"
                                class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-sm shadow-lg hover:bg-blue-600 transition duration-300">
                                Confirmar Retiro Medicamento
                            </button>
                        </div>
                    </form>
                </div>

                {{-- FORMULARIO 2: INSUMOS MÉDICOS --}}
                <div x-show="tipoForm === 'insumo'" x-cloak class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm">
                    <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                        <i class="fas fa-minus-circle text-blue-500"></i> Retirar Insumo Médico
                    </h2>
                    <form @submit.prevent="registrarRetiroIns()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Insumo Médico</label>
                                <select x-model="formIns.insumo_id"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Seleccione insumo médico</option>
                                    <template x-for="ins in insumos" :key="ins.id">
                                        <option :value="ins.id" x-text="ins.nombre"></option>
                                    </template>
                                </select>
                                <template x-if="stockIns() !== null">
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        Stock disponible: <span class="text-blue-600 font-bold" x-text="stockIns()"></span>
                                    </p>
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Área de Retiro</label>
                                <select x-model="formIns.area_id"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Seleccione área</option>
                                    <template x-for="area in areas" :key="area.id">
                                        <option :value="area.id" x-text="area.nombre_area"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Cantidad a Retirar</label>
                                <input type="number" x-model="formIns.cantidad" min="1" placeholder="Ej. 10"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit"
                                class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-sm shadow-lg hover:bg-blue-600 transition duration-300">
                                Confirmar Retiro Insumo
                            </button>
                        </div>
                    </form>
                </div>

            </div>

            {{-- Tabla de Retiros --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-800">Retiros de Hoy</h2>
                    </div>
                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-[600px] text-left border-collapse">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                                <tr class="border-b border-slate-100">
                                    <th class="px-6 py-4 whitespace-nowrap">Ítem</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Tipo</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Área</th>
                                    <th class="px-6 py-4 text-center whitespace-nowrap">Cant.</th>
                                    <th class="px-6 py-4 text-center whitespace-nowrap">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="retiro in retiros" :key="retiro.id">
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-6 py-5 font-bold text-slate-800 whitespace-nowrap"
                                            x-text="retiro.nombre"></td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <span :class="retiro.tipo.toLowerCase().includes('insumo') ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700'"
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">
                                                <i :class="retiro.tipo.toLowerCase().includes('insumo') ? 'fas fa-box-tissue' : 'fas fa-pills'" class="text-[10px]"></i>
                                                <span x-text="retiro.tipo"></span>
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-slate-600 text-sm whitespace-nowrap"
                                            x-text="retiro.nombre_area"></td>
                                        <td class="px-6 py-5 text-center whitespace-nowrap">
                                            <span class="bg-gray-50 text-black-600 px-2 py-1 rounded-sm font-bold"
                                                x-text="retiro.cantidad"></span>
                                        </td>
                                        <td class="px-6 py-5 text-center text-xs text-slate-400 whitespace-nowrap"
                                            x-text="retiro.created_at"></td>
                                    </tr>
                                </template>
                                <template x-if="retiros.length === 0">
                                    <tr>
                                        <td colspan="5"
                                            class="px-6 py-12 text-center text-slate-400 italic whitespace-nowrap">No se han
                                            registrado retiros hoy.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de Stock Insuficiente --}}
        <div x-show="modalStock" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @keydown.escape.window="cerrarModalStock()">
            
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 border border-slate-100 relative overflow-hidden" @click.away="cerrarModalStock()">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-xl font-bold">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Stock Insuficiente</h3>
                        <p class="text-xs text-slate-500">No se puede procesar la cantidad solicitada</p>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-lg p-4 mb-5 border border-slate-100 space-y-2">
                    <div class="text-sm font-bold text-slate-800" x-text="modalStockData.nombre"></div>
                    <div class="flex justify-between items-center text-xs pt-2 border-t border-slate-200/60">
                        <span class="text-slate-500">Cantidad solicitada:</span>
                        <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded" x-text="modalStockData.solicitado + ' unids'"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500">Stock disponible en almacén:</span>
                        <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded" x-text="modalStockData.disponible + ' unids'"></span>
                    </div>
                </div>

                <button @click="cerrarModalStock()" type="button" class="w-full bg-slate-900 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-slate-800 transition duration-200 shadow-md">
                    Entendido
                </button>
            </div>
        </div>

    </div>
@endsection