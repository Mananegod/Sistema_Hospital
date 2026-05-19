@extends('layouts.app')

@section('title', 'Gestión F15 - Pacientes Internados')

@section('content')
@include('components.mensajes-notificaciones')
@include('components.loading-overlay')

<div class="max-w-7xl mx-auto" x-data="{
    // Inyección de pacientes reales traídos desde el controlador de Laravel
    pacientes: [
        @foreach($pacientes as $paciente)
            {
                id: {{ $paciente->id }},
                cedula: '{{ $paciente->cedula }}',
                nombre: '{{ $paciente->nombre_apellido }}',
                edad: {{ $paciente->edad }},
                servicio: '{{ $paciente->servicio }}',
                diagnostico: '{{ $paciente->diagnostico }}',
                fecha_ingreso: '{{ \Carbon\Carbon::parse($paciente->fecha_ingreso)->format('d/m/Y') }}'
            },
        @endforeach
    ],

    // Estado local para los campos del formulario
    form: {
        cedula: '',
        nombre_apellido: '',
        edad: '',
        area_id: '',
        diagnostico: '',
        tratamiento: '',
        fecha_ingreso: '{{ date('Y-m-d') }}'
    },

    // Enviar el formulario a Laravel de manera nativa
    registrarPaciente() {
        if(!this.form.cedula || !this.form.nombre_apellido || !this.form.edad || !this.form.area_id || !this.form.diagnostico) {
            if (this.$store.toast) this.$store.toast.add('Por favor complete todos los campos obligatorios.', 'error');
            return;
        }

        if (this.$store.loading) {
            this.$store.loading.activate('Registrando ingreso de paciente...');
        }

        let formSubmit = document.createElement('form');
        formSubmit.method = 'POST';
        formSubmit.action = '{{ route('pacientes.store') }}';

        // Token CSRF reglamentario
        let csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        formSubmit.appendChild(csrfInput);

        // Mapear campos de Alpine al Form HTML
        for (let key in this.form) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = this.form[key];
            formSubmit.appendChild(input);
        }

        document.body.appendChild(formSubmit);
        formSubmit.submit();
    }
}">

    {{-- Bloque de Encabezado --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Ingreso F15 - Registro Diario</h1>
        <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Gestión de pacientes hospitalizados vinculados a la hoja de despacho de insumos médicos.</p>
    </div>

    {{-- Formulario Principal de Carga --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden mb-10">
        <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs">
                <i class="fas fa-plus"></i>
            </div>
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Ficha de Ingreso Médico</h3>
        </div>
        
        <form @submit.prevent="registrarPaciente()" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Cédula --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Cédula de Identidad</label>
                    <input type="text" x-model="form.cedula" placeholder="Ej: 24123456" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                {{-- Nombre --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Nombre y Apellido</label>
                    <input type="text" x-model="form.nombre_apellido" placeholder="Nombre completo del paciente" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                {{-- Edad (Campo de Hoja de Servicio Excel) --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Edad del Paciente</label>
                    <input type="number" x-model="form.edad" min="0" max="125" placeholder="Ej: 28" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Servicio / Área Destino (Campo de Hoja de Servicio Excel) --}}
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Servicio / Área Hospitalaria</label>
                    <select x-model="form.area_id" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="" disabled selected>Seleccione el servicio...</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Diagnóstico --}}
                <div class="space-y-2 md:col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Diagnóstico Inicial (Dx)</label>
                    <input type="text" x-model="form.diagnostico" placeholder="Indique el diagnóstico clínico..." class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>

            {{-- Tratamiento / Insumos Solicitados (Campo de Hoja de Servicio Excel) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-3 space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Tratamiento Médico / Descripción de Insumos</label>
                    <textarea x-model="form.tratamiento" rows="2" placeholder="Escriba detalladamente el tratamiento o insumos de almacén requeridos..." class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 resize-none"></textarea>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Fecha Ingreso</label>
                    <input type="date" x-model="form.fecha_ingreso" class="w-full bg-slate-50 p-4 rounded-2xl border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
            </div>

            {{-- Botón de Envío --}}
            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-bold text-xs uppercase tracking-[0.2em] hover:bg-blue-600 transition duration-300 shadow-xl shadow-slate-900/10">
                    Registrar e Internar Paciente
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de Pacientes Registrados Hoy --}}
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">
                    <i class="fas fa-list"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Pacientes Registrados Hoy</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-50/20">
                        <th class="p-6">Cédula</th>
                        <th class="p-6">Paciente</th>
                        <th class="p-6 text-center">Edad</th>
                        <th class="p-6">Servicio / Área</th>
                        <th class="p-6">Diagnóstico</th>
                        <th class="p-6 text-center">Ingreso</th>
                        <th class="p-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                    {{-- Renderizado Reactivo con Alpine --}}
                    <template x-for="p in pacientes" :key="p.id">
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="p-6 text-slate-400 font-bold" x-text="p.cedula"></td>
                            <td class="p-6 font-bold text-slate-900" x-text="p.nombre"></td>
                            <td class="p-6 text-center" x-text="p.edad + ' años'"></td>
                            <td class="p-6">
                                <span class="bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded-xl font-bold uppercase tracking-wider" x-text="p.servicio"></span>
                            </td>
                            <td class="p-6 text-slate-500 max-w-xs truncate" x-text="p.diagnostico"></td>
                            <td class="p-6 text-center text-xs text-slate-400 font-semibold" x-text="p.fecha_ingreso"></td>
                            <td class="p-6 text-right">
                                <a :href="'/pacientes/' + p.id + '/pdf'" class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-red-50 hover:text-red-600 transition duration-200">
                                    <i class="fas fa-file-pdf text-sm"></i> Imprimir F15
                                </a>
                            </td>
                        </tr>
                    </template>

                    {{-- Vacío --}}
                    <template x-if="pacientes.length === 0">
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400 italic font-medium">
                                No se han cargado admisiones o ingresos de pacientes el día de hoy.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection