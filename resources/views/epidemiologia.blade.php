@extends('layouts.app')

@section('title', 'Vigilancia Epidemiológica')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Encabezado --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Epidemiológica</h1>
        <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Hospital "Dr. Tiburcio Garrido" - Registro de Patologías de Notificación Obligatoria.</p>
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

    {{-- Contenedor General con Alpine --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8"
         x-data="{ 
            editOpen: false, 
            caso: { id: '', nombre_paciente: '', cedula_paciente: '', patologia_cie10: '', sector_procedencia: '', fecha_sintomas: '', estado_caso: '', observaciones: '' },
            abrirEditar(item) {
                this.caso = { ...item };
                this.editOpen = true;
            }
         }">

        {{-- Panel Izquierdo: Formulario de Registro --}}
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm sticky top-6">
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <span class="w-2 h-6 bg-rose-600 rounded-full"></span> Nueva Alerta
                </h2>

                <form action="{{ route('epidemiologia.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Nombre del Paciente</label>
                        <input type="text" name="nombre_paciente" required value="{{ old('nombre_paciente') }}"
                            
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Cédula (Opcional)</label>
                        <input type="text" name="cedula_paciente" value="{{ old('cedula_paciente') }}"
                            
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Diagnóstico / Patología</label>
                        <input type="text" name="patologia_cie10" required value="{{ old('patologia_cie10') }}"
                            
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm font-semibold">
                    </div>

                    {{-- MODIFICADO: SELECT DINÁMICO DE SECTORES OFFLINE --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Sector / Comunidad</label>
                        <select name="sector_procedencia" required class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm font-semibold">
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
                            <input type="date" name="fecha_sintomas" required value="{{ old('fecha_sintomas') }}"
                                class="w-full bg-slate-50 border-0 rounded-xl px-3 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Estado</label>
                            <select name="estado_caso" required class="w-full bg-slate-50 border-0 rounded-xl px-3 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-xs font-bold">
                                <option value="SOSPECHOSO">SOSPECHOSO</option>
                                <option value="PROBABLE">PROBABLE</option>
                                <option value="CONFIRMADO">CONFIRMADO</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 ml-1 uppercase">Observaciones Clínicas</label>
                        <textarea name="observaciones" rows="2" placeholder="Síntomas..."
                            class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 shadow-sm focus:ring-2 focus:ring-rose-500 text-sm"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition-all transform hover:-translate-y-1 uppercase tracking-wider text-xs">
                        Registrar Ficha de Alerta
                    </button>
                </form>
            </div>
        </div>

        {{-- Panel Derecho: Listado de Casos --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Paciente / Origen</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Patología</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Estado</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($casos as $c)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-700 block text-sm">{{ $c->nombre_paciente }}</span>
                                <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider block">
                                    <i class="fas fa-map-marker-alt text-rose-500 mr-1"></i> {{ $c->sector_procedencia }}
                                </span>
                                @if($c->cedula_paciente)
                                <span class="text-[9px] font-mono text-slate-400 block">Doc: {{ $c->cedula_paciente }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-800 uppercase block">{{ $c->patologia_cie10 }}</span>
                                <span class="text-[10px] text-slate-400 block">Síntomas: {{ \Carbon\Carbon::parse($c->fecha_sintomas)->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black tracking-wider
                                    {{ $c->estado_caso == 'CONFIRMADO' ? 'bg-red-100 text-red-600' : ($c->estado_caso == 'PROBABLE' ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600') }}">
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

        {{-- MODAL OFFLINE - EXPEDIENTE --}}
        <div x-show="editOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             x-transition>
            
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-slate-100 max-w-md w-full"
                 @click.away="editOpen = false">
                
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

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Observaciones / Síntomas</span>
                        <p class="text-xs text-slate-600 leading-relaxed italic" x-text="caso.observaciones || 'Sin anotaciones clínicas adicionales.'"></p>
                    </div>

                    <div class="mt-6">
                        <button type="button" @click="editOpen = false"
                            class="w-full bg-slate-100 text-slate-700 font-bold py-3 rounded-xl hover:bg-slate-200 transition text-xs uppercase tracking-wider">
                            Cerrar Expediente
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection