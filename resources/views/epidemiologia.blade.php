@extends('layouts.app')

@section('title', 'Vigilancia Epidemiológica')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Encabezado Principal --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Vigilancia Epidemiológica</h1>
        <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Hospital "Dr. Tiburcio Garrido" - Análisis Dinámico de Curvas y Canales Endémicos</p>
    </div>

    {{-- Notificación de Éxito --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-sm shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm font-semibold">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- Notificaciones de Errores de Validación --}}
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-sm shadow-sm">
        <div class="flex items-center gap-2 mb-2 font-bold text-sm">
            <i class="fa-solid fa-circle-exclamation text-red-600"></i>
            <span>Por favor, corrige los siguientes campos:</span>
        </div>
        <ul class="list-disc pl-5 text-xs font-medium space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Contenedor de Gestión de Casos (Grid Superior) con Estado Global de Alpine --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8"
         x-data="{ 
            modo: 'crear',
            editOpen: false, 
            caso: { id: '', nombre_paciente: '', cedula_paciente: '', patologia_cie10: '', sector_procedencia: '', fecha_sintomas: '', estado_caso: '', observaciones: '' },
            abrirEditar(item) {
                this.caso = { ...item };
                this.editOpen = true;
            }
         }">

        {{-- Barra Lateral Izquierda: Alternador Dinámico de Contenedores --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Botón de Acción Principal --}}
            <div>
                <button type="button" 
                        x-show="modo === 'crear'"
                        @click="modo = 'importar'"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-sm shadow-sm transition-all flex items-center justify-center gap-2 uppercase tracking-wider text-xs">
                    <i class="fa-solid fa-file-import"></i> Importar Datos Históricos
                </button>

                <button type="button" 
                        x-show="modo === 'importar'"
                        @click="modo = 'crear'"
                        class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-4 rounded-sm shadow-sm transition-all flex items-center justify-center gap-2 uppercase tracking-wider text-xs"
                        x-cloak>
                    <i class="fa-solid fa-plus"></i> Crear Nuevo Registro
                </button>
            </div>

            {{-- 1. CONTENEDOR ESTÁNDAR: NUEVA ALERTA --}}
            <div x-show="modo === 'crear'" 
                 class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0">
                
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-rose-600 rounded-full"></span> Nueva Alerta
                </h2>

                <form action="{{ route('epidemiologia.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Nombre del Paciente</label>
                        <input type="text" name="nombre_paciente" required 
                               pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+" 
                               maxlength="25"
                               title="Solo se permiten letras y espacios (máx. 25 caracteres)"
                               value="{{ old('nombre_paciente') }}"
                               class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Cédula</label>
                        <input type="number" name="cedula_paciente" required 
                               pattern="[0-9]{1,10}" 
                               maxlength="10"
                               inputmode="numeric" 
                               value="{{ old('cedula_paciente') }}"
                               title="La cédula debe ser numérica y tener un máximo de 10 dígitos"
                               class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Diagnóstico / Patología</label>
                        <input type="text" name="patologia_cie10" value="{{ old('patologia_cie10') }}" required placeholder="EJ: DENGUE"
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Sector / Comunidad</label>
                        <select name="sector_procedencia" required class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm font-semibold">
                            <option value="" disabled selected>Seleccionar sector...</option>
                            @foreach($sectores as $sec)
                                <option value="{{ $sec->nombre_sector }}" {{ old('sector_procedencia') == $sec->nombre_sector ? 'selected' : '' }}>
                                    {{ $sec->nombre_sector }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Inicio Síntomas</label>
                            <input type="date" name="fecha_sintomas" max="2036-12-31" required value="{{ old('fecha_sintomas') }}"
                                class="w-full bg-slate-50 border-0 rounded-sm px-3 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Estado</label>
                            <select name="estado_caso" required class="w-full bg-slate-50 border-0 rounded-sm px-3 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-xs font-bold">
                                <option value="SOSPECHOSO" {{ old('estado_caso') == 'SOSPECHOSO' ? 'selected' : '' }}>SOSPECHOSO</option>
                                <option value="EN ESPERA" {{ old('estado_caso') == 'EN ESPERA' ? 'selected' : '' }}>EN ESPERA</option>
                                <option value="CONFIRMADO" {{ old('estado_caso') == 'CONFIRMADO' ? 'selected' : '' }}>CONFIRMADO</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Observaciones Clínicas</label>
                        <textarea name="observaciones" rows="2" placeholder="Describa síntomas claves de vigilancia..." required
                            class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm">{{ old('observaciones') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-4 rounded-sm shadow-lg hover:bg-slate-800 transition-all transform hover:-translate-y-0.5 uppercase tracking-wider text-xs">
                        Registrar Ficha de Alerta
                    </button>
                </form>
            </div>

            {{-- 2. CONTENEDOR IDENTICO: IMPORTAR DATOS HISTÓRICOS --}}
            <div x-show="modo === 'importar'" 
                 class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0">
                
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-600 rounded-full"></span> Importar Datos
                </h2>

                <p class="text-xs text-slate-500 mb-5 leading-relaxed">
                    Carga un archivo <strong class="text-slate-700">.CSV</strong> estructurado con casos de años pasados para actualizar automáticamente el canal endémico dinámico.
                </p>

                <form action="{{ route('epidemiologia.importar') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 ml-1 uppercase">Seleccionar Archivo</label>
                        <div class="bg-slate-50 p-4 rounded-sm border border-slate-100">
                            <input type="file" name="archivo_csv" accept=".csv" required
                                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-2 ml-1 uppercase">Orden de columnas esperado</label>
                        <div class="bg-slate-50 p-3.5 rounded-sm border border-slate-100">
                            <code class="font-mono text-[9px] block bg-slate-200/60 text-slate-700 p-2 rounded-sm select-all break-all leading-normal">
                                patologia,año,semana,exito,seguridad,alerta,epidemia,actual
                            </code>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="modo = 'crear'"
                                class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-sm transition text-xs uppercase tracking-wider">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="w-1/2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-sm shadow-md transition text-xs uppercase tracking-wider">
                            Procesar CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panel Derecho: Listado Principal de Fichas Registradas --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden h-full">
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-[600px] text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Paciente / Origen</th>
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Patología</th>
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Estado</th>
                                <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($casos as $c)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-700 block text-sm whitespace-nowrap">{{ $c->nombre_paciente }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider block whitespace-nowrap">
                                        <i class="fas fa-map-marker-alt text-rose-500 mr-1"></i> {{ $c->sector_procedencia }}
                                    </span>
                                    @if($c->cedula_paciente)
                                    <span class="text-[9px] font-mono text-slate-400 block">Doc: {{ $c->cedula_paciente }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-800 uppercase block whitespace-nowrap">{{ $c->patologia_cie10 }}</span>
                                    <span class="text-[10px] text-slate-400 block whitespace-nowrap">Síntomas: {{ \Carbon\Carbon::parse($c->fecha_sintomas)->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-sm text-[10px] font-black tracking-wider inline-block whitespace-nowrap
                                        {{ $c->estado_caso == 'CONFIRMADO' ? 'bg-red-100 text-red-600' : ($c->estado_caso == 'EN ESPERA' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600') }}">
                                        {{ $c->estado_caso }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" 
                                                @click="abrirEditar({
                                                    id: '{{ $c->id }}',
                                                    nombre_paciente: '{{ $c->nombre_paciente }}',
                                                    cedula_paciente: '{{ $c->cedula_paciente }}',
                                                    patologia_cie10: '{{ $c->patologia_cie10 }}',
                                                    sector_procedencia: '{{ $c->sector_procedencia }}',
                                                    fecha_sintomas: '{{ $c->fecha_sintomas }}',
                                                    estado_caso: '{{ $c->estado_caso }}',
                                                    observaciones: '{{ $c->observaciones }}'
                                                })" 
                                                class="p-2 text-slate-400 hover:text-blue-600 transition"
                                                title="Ver Ficha Detallada">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        
                                        <form action="{{ route('epidemiologia.destroy', $c->id) }}" method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition" onclick="return confirm('¿Eliminar esta alerta del sistema?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs font-medium uppercase tracking-wider">
                                    No se reportan alertas ni focos epidemiológicos activos el día de hoy.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETALLE EXPEDIENTE --}}
    <div x-show="editOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
         x-transition>
        <div class="bg-white p-6 rounded-sm shadow-xl border border-slate-100 max-w-md w-full" @click.away="editOpen = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-file-medical text-rose-600"></i> Ficha Epidemiológica
                </h3>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="space-y-4 text-left">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Paciente</span>
                    <p class="text-sm font-bold text-slate-800" x-text="caso.nombre_paciente"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Cédula</span>
                        <p class="text-xs font-mono text-slate-700" x-text="caso.cedula_paciente || 'No registrado'"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Sector</span>
                        <p class="text-xs font-bold text-slate-700" x-text="caso.sector_procedencia"></p>
                    </div>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase">Patología Bajo Vigilancia</span>
                    <p class="text-xs font-black text-rose-600 uppercase" x-text="caso.patologia_cie10"></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Inicio de Síntomas</span>
                        <p class="text-xs text-slate-700 font-medium" x-text="caso.fecha_sintomas"></p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Estatus actual</span>
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-black text-white bg-slate-900" x-text="caso.estado_caso"></span>
                    </div>
                </div>
                <div class="bg-slate-50 p-3 rounded-sm border border-slate-100">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Observaciones / Síntomas</span>
                    <p class="text-xs text-slate-600 leading-relaxed italic" x-text="caso.observaciones || 'Sin anotaciones clínicas adicionales.'"></p>
                </div>
                <div class="mt-6">
                    <button type="button" @click="editOpen = false"
                        class="w-full bg-slate-100 text-slate-700 font-bold py-3 rounded-sm hover:bg-slate-200 transition text-xs uppercase tracking-wider">
                        Cerrar Expediente
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- CANALES ENDÉMICOS ESTADÍSTICA --}}
    <div class="bg-white rounded-sm border border-slate-100 shadow-sm p-6 mt-8"
         x-data="{
            patologiaSeleccionada: '{{ $patologias->first() }}',
            anoSeleccionado: '{{ $anos->first() }}',
            semanaPrediccion: 1,
            canalesData: {{ json_encode($datosCanales) }},
            chartInstance: null,

            init() {
                this.renderizarCanal();
                this.$watch('patologiaSeleccionada', () => this.renderizarCanal());
                this.$watch('anoSeleccionado', () => this.renderizarCanal());
            },

            obtenerDatosActuales() {
                return this.canalesData[this.patologiaSeleccionada]?.[this.anoSeleccionado] || {
                    actual: Array(52).fill(0),
                    exito: Array(52).fill(0),
                    seguridad: Array(52).fill(0),
                    alerta: Array(52).fill(0),
                    epidemia: Array(52).fill(0),
                    conteo_real: Array(52).fill(0),
                    medicamentos: [],
                    es_historico: false
                };
            },

            evaluarAlertaEpidemiologica(casos, alerta, epidemia) {
                if (!casos || casos === 0) {
                    return { texto: 'SITUACIÓN: SIN ALERTAS REPORTADAS', clase: 'bg-slate-100 text-slate-700' };
                }
                if (casos > epidemia) {
                    return { texto: 'SITUACIÓN: EPIDEMIA', clase: 'bg-red-500 text-white animate-pulse' };
                } else if (casos > alerta) {
                    return { texto: 'SITUACIÓN: ALERTA', clase: 'bg-amber-500 text-white' };
                }
                return { texto: 'SITUACIÓN: CONTROLADO (BAJO LA CURVA)', clase: 'bg-emerald-500 text-white' };
            },

            renderizarCanal() {
                const ctx = document.getElementById('canvasCanalEndemico').getContext('2d');
                const datos = this.obtenerDatosActuales();
                const semanas = Array.from({length: 52}, (_, i) => i + 1);

                if (this.chartInstance) {
                    this.chartInstance.destroy();
                }

                this.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: semanas,
                        datasets: [
                            {
                                label: 'Casos Reales Registrados',
                                data: datos.actual,
                                borderColor: '#0f172a',
                                backgroundColor: 'rgba(15, 23, 42, 0.1)',
                                borderWidth: 3,
                                type: 'bar',
                                order: 1
                            },
                            {
                                label: 'Zona de Éxito (Q1)',
                                data: datos.exito,
                                borderColor: 'rgba(16, 185, 129, 0.4)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                pointRadius: 0,
                                borderWidth: 1.5,
                                order: 5
                            },
                            {
                                label: 'Zona de Seguridad (Q2)',
                                data: datos.seguridad,
                                borderColor: 'rgba(59, 130, 246, 0.4)',
                                backgroundColor: 'rgba(59, 130, 246, 0.15)',
                                fill: true,
                                pointRadius: 0,
                                borderWidth: 1.5,
                                order: 4
                            },
                            {
                                label: 'Zona de Alerta (Q3)',
                                data: datos.alerta,
                                borderColor: 'rgba(245, 158, 11, 0.4)',
                                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                                fill: true,
                                pointRadius: 0,
                                borderWidth: 1.5,
                                order: 3
                            },
                            {
                                label: 'Zona de Epidemia',
                                data: datos.epidemia,
                                borderColor: 'rgba(239, 68, 68, 0.5)',
                                backgroundColor: 'rgba(239, 68, 68, 0.25)',
                                fill: true,
                                pointRadius: 0,
                                borderWidth: 1.5,
                                order: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Casos', font: { weight: 'bold' } }
                            },
                            x: {
                                title: { display: true, text: 'Semanas Epidemiológicas', font: { weight: 'bold' } }
                            }
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                        }
                    }
                });
            }
         }">
        
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-xl font-black text-slate-900 flex items-center gap-2 uppercase">
                <i class="fa-solid fa-chart-area text-rose-600"></i> Consulta Dinámica de Canales Importados de Archivos Físicos
            </h2>
            <p class="text-slate-400 text-xs mt-1 uppercase tracking-wider">Visualiza las curvas de tus carpetas digitalizadas de años anteriores y revisa el protocolo de fármacos asignado.</p>
        </div>

        {{-- Selectores de Filtro superior --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 tracking-wider">Patología en Vigilancia</label>
                <select x-model="patologiaSeleccionada" class="w-full bg-slate-50 border-0 rounded-sm py-3 px-4 text-xs font-bold text-slate-700 uppercase focus:ring-2 focus:ring-rose-500">
                    @foreach($patologias as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 tracking-wider">Año de Histórico</label>
                <select x-model="anoSeleccionado" class="w-full bg-slate-50 border-0 rounded-sm py-3 px-4 text-xs font-bold text-slate-700 uppercase focus:ring-2 focus:ring-rose-500">
                    @foreach($anos as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1.5 tracking-wider">Semana Epidemiológica a Inspeccionar (1-52)</label>
                <input type="number" min="1" max="52" x-model.number="semanaPrediccion" 
                       class="w-full bg-slate-50 border-0 rounded-sm py-2.5 px-4 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-rose-500">
            </div>
        </div>

        {{-- Gráfico --}}
        <div class="w-full overflow-x-auto mb-6 border border-slate-100 rounded-sm p-4 bg-slate-50">
            <div class="min-w-[800px] h-[320px] relative">
                <canvas id="canvasCanalEndemico"></canvas>
            </div>
        </div>

        {{-- Alertas y Medicamentos Proyectados --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            
            {{-- Tarjeta de Estatus Sanitario --}}
            <div class="md:col-span-5 bg-slate-50 p-5 rounded-sm border border-slate-100 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Análisis de Alertas</span>
                    <h4 class="text-xs font-bold text-slate-500 uppercase">Semana Epidemiológica N° <span class="text-slate-800 font-extrabold" x-text="semanaPrediccion"></span></h4>
                    
                    <div class="mt-4 p-4 rounded-sm text-center font-black text-xs tracking-wider uppercase"
                         :class="evaluarAlertaEpidemiologica(obtenerDatosActuales().actual[semanaPrediccion - 1], obtenerDatosActuales().alerta[semanaPrediccion - 1], obtenerDatosActuales().epidemia[semanaPrediccion - 1]).clase">
                        <span x-text="evaluarAlertaEpidemiologica(obtenerDatosActuales().actual[semanaPrediccion - 1], obtenerDatosActuales().alerta[semanaPrediccion - 1], obtenerDatosActuales().epidemia[semanaPrediccion - 1]).texto"></span>
                    </div>
                </div>

                <div class="mt-6 space-y-2 border-t border-slate-200/60 pt-4">
                    <template x-if="obtenerDatosActuales().es_historico">
                        <div class="bg-blue-50 text-blue-700 p-2.5 rounded-sm text-[10px] font-semibold mb-2">
                            <i class="fa-solid fa-circle-info mr-1"></i> Mostrando curvas reales de las carpetas físicas importadas.
                        </div>
                    </template>
                    
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500 font-semibold">Personas Registradas:</span>
                        <span class="font-extrabold text-slate-800" x-text="obtenerDatosActuales().conteo_real ? obtenerDatosActuales().conteo_real[semanaPrediccion - 1] : 0"></span>
                    </div>
                    
                    <div class="flex justify-between text-xs border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Equivalente en Gráfico (1 por c/5 p.):</span>
                        <span class="font-extrabold text-indigo-600" x-text="obtenerDatosActuales().actual[semanaPrediccion - 1] || 0"></span>
                    </div>
                    
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400 font-medium">Límite de Alerta Importado (Q3):</span>
                        <span class="font-bold text-amber-600" x-text="obtenerDatosActuales().alerta[semanaPrediccion - 1] || 0"></span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400 font-medium">Límite de Epidemia Importado:</span>
                        <span class="font-bold text-red-600" x-text="obtenerDatosActuales().epidemia[semanaPrediccion - 1] || 0"></span>
                    </div>
                </div>
            </div>

            {{-- Tarjeta de Medicamentos --}}
            <div class="md:col-span-7 bg-white text-slate-900 p-5 rounded-sm border border-slate-100 shadow-sm flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest block mb-2">Protocolo Farmacéutico Establecido</span>
                    <h4 class="text-xs font-bold text-slate-800 uppercase">Medicamentos e Insumos de Uso Oficial</h4>
                    <p class="text-[10px] text-slate-500 mt-1">Este listado especifica las dosis estándar indicadas para el manejo clínico de la patología seleccionada.</p>
                </div>

                <div class="mt-4 space-y-3">
                    <template x-for="med in obtenerDatosActuales().medicamentos" :key="med.nombre">
                        <div class="flex flex-col border-b border-slate-100 pb-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold uppercase text-slate-700" x-text="med.nombre"></span>
                                <span class="text-[10px] font-bold text-emerald-600 uppercase" x-text="med.unidad"></span>
                            </div>
                            <span class="text-[10px] text-slate-500 mt-0.5" x-text="med.indicacion"></span>
                        </div>
                    </template>
                </div>

                <div class="bg-slate-50 p-2.5 mt-4 rounded-sm text-[10px] text-slate-600 leading-normal flex items-start gap-1.5 border border-slate-100/80">
                    <i class="fa-solid fa-shield-halved text-emerald-600 mt-0.5"></i>
                    <span>Los canales históricos no calculan stock predictivo para evitar falsas proyecciones sobre historiales médicos cerrados.</span>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- Cargamos Chart.js de forma integrada en tu sistema --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection