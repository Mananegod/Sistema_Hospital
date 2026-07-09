@extends('layouts.app')

@section('title', 'Pacientes Internados')

@section('content')
@include('components.mensajes-notificaciones')
@include('components.loading-overlay')

<div class="max-w-7xl mx-auto" x-data="{
    // Inyección de pacientes reales traídos desde el controlador de Laravel
    pacientes: [
        @foreach($pacientes as $paciente_item)
            {
                id: {{ $paciente_item->id }},
                cedula: '{{ $paciente_item->cedula }}',
                nombre: '{{ $paciente_item->nombre_apellido }}',
                edad: {{ $paciente_item->edad }},
                area_id: {{ $paciente_item->area_id }},
                servicio: '{{ $paciente_item->servicio }}',
                diagnostico: '{{ $paciente_item->diagnostico }}',
                tratamiento: '{{ $paciente_item->tratamiento ?? '' }}',
                fecha_ingreso: '{{ $paciente_item->fecha_ingreso }}',
                fecha_formateada: '{{ \Carbon\Carbon::parse($paciente_item->fecha_ingreso)->format('d/m/Y') }}'
            },
        @endforeach
    ],

    // Estado local para los campos del formulario (Crear/Editar)
    form: {
        id: null,
        cedula: '',
        nombre_apellido: '',
        edad: '',
        area_id: '',
        diagnostico: '',
        tratamiento: '',
        fecha_ingreso: '{{ date('Y-m-d') }}'
    },

    // Control de Modales
    modalVer: false,
    modalEditar: false,
    modalEliminar: false,
    pacienteSeleccionado: {},

    // Abrir modal de visualización
    verPaciente(paciente) {
        this.pacienteSeleccionado = paciente;
        this.modalVer = true;
    },

    // Abrir modal de edición pre-cargando los datos
    editarPaciente(paciente) {
        this.form.id = paciente.id;
        this.form.cedula = paciente.cedula;
        this.form.nombre_apellido = paciente.nombre;
        this.form.edad = paciente.edad;
        this.form.area_id = paciente.area_id;
        this.form.diagnostico = paciente.diagnostico;
        this.form.tratamiento = paciente.tratamiento;
        this.form.fecha_ingreso = paciente.fecha_ingreso;
        this.modalEditar = true;
    },

    // Abrir confirmación de eliminación
    confirmarEliminar(paciente) {
        this.pacienteSeleccionado = paciente;
        this.modalEliminar = true;
    },

    // Enviar el formulario (Guardar Nuevo)
    registrarPaciente() {
        if(!this.form.cedula || !this.form.nombre_apellido || !this.form.edad || !this.form.area_id || !this.form.diagnostico) {
            alert('Por favor complete todos los campos obligatorios.');
            return;
        }
        
        // Validaciones preventivas en Frontend
        if(/[a-zA-Z]/.test(this.form.cedula)) {
            alert('La cédula de identidad debe contener únicamente números.');
            return;
        }
        if(/[0-9]/.test(this.form.nombre_apellido)) {
            alert('El nombre y apellido no puede contener números.');
            return;
        }
        let añoIngreso = new Date(this.form.fecha_ingreso).getFullYear();
        if(añoIngreso > 2036) {
            alert('La fecha de ingreso no puede ser posterior al año 2036.');
            return;
        }

        this.submitForm('{{ route('pacientes.store') }}');
    },

    // Enviar formulario de Actualización
    actualizarPaciente() {
        if(/[a-zA-Z]/.test(this.form.cedula)) {
            alert('La cédula de identidad debe contener únicamente números.');
            return;
        }
        if(/[0-9]/.test(this.form.nombre_apellido)) {
            alert('El nombre y apellido no puede contener números.');
            return;
        }
        let añoIngreso = new Date(this.form.fecha_ingreso).getFullYear();
        if(añoIngreso > 2036) {
            alert('La fecha de ingreso no puede ser posterior al año 2036.');
            return;
        }

        this.submitForm('/pacientes/' + this.form.id + '/update');
    },

    // Ejecutar eliminación real
    eliminarPaciente() {
        this.submitForm('/pacientes/' + this.pacienteSeleccionado.id + '/delete');
    },

    // Función auxiliar para procesar los envíos nativos
    submitForm(actionUrl) {
        if (this.$store.loading) this.$store.loading.activate('Procesando solicitud...');
        
        let formSubmit = document.createElement('form');
        formSubmit.method = 'POST';
        formSubmit.action = actionUrl;

        // Token CSRF
        let csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        formSubmit.appendChild(csrfInput);

        // Adjuntar campos del formulario actual
        for (let key in this.form) {
            if(this.form[key] !== null) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = this.form[key];
                formSubmit.appendChild(input);
            }
        }

        document.body.appendChild(formSubmit);
        formSubmit.submit();
    }
}">

    {{-- Bloque de Encabezado --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Registro Diario</h1>
        <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Gestión de pacientes hospitalizados vinculados a la hoja de despacho de insumos médicos.</p>
    </div>

    {{-- Formulario Principal de Carga --}}
    <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden mb-10">
        <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex items-center gap-3">
            <div class="w-8 h-8 rounded-sm bg-slate-900 text-white flex items-center justify-center text-xs">
                <i class="fas fa-plus"></i>
            </div>
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Ficha de Insumos Médico</h3>
        </div>
        
        <form @submit.prevent="registrarPaciente()" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Cédula de Identidad</label>
                    <input type="text" x-model="form.cedula" placeholder="Ej: 24123456" class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Nombre y Apellido</label>
                    <input type="text" x-model="form.nombre_apellido" placeholder="Nombre completo del paciente" class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Edad del Paciente</label>
                    <input type="number" x-model="form.edad" min="0" max="125" placeholder="Ej: 28" class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Servicio / Área Hospitalaria</label>
                    <select x-model="form.area_id" class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                        <option value="" disabled selected>Seleccione el servicio...</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Diagnóstico Inicial (Dx)</label>
                    <input type="text" x-model="form.diagnostico" placeholder="Indique el diagnóstico clínico..." class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-3">
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Tratamiento Médico / Descripción de Insumos</label>
                    <textarea x-model="form.tratamiento" rows="2" placeholder="Escriba detalladamente el tratamiento o insumos..." class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 resize-none mt-2"></textarea>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Fecha Ingreso</label>
                    <input type="date" x-model="form.fecha_ingreso" class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-slate-900 text-white px-10 py-5 rounded-sm font-bold text-xs uppercase tracking-[0.2em] hover:bg-blue-600 transition duration-300 shadow-xl shadow-slate-900/10">
                    Registrar e Internar Paciente
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de Pacientes Registrados --}}
    <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-sm bg-blue-600 text-white flex items-center justify-center text-xs">
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
                        <th class="p-6 text-center">Ingreso</th>
                        <th class="p-6 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                    <template x-for="p in pacientes" :key="p.id">
                        <tr class="hover:bg-slate-50/50 transition duration-150">
                            <td class="p-6 text-slate-400 font-bold" x-text="p.cedula"></td>
                            <td class="p-6 font-bold text-slate-900" x-text="p.nombre"></td>
                            <td class="p-6 text-center" x-text="p.edad + ' años'"></td>
                            <td class="p-6">
                                <span class="bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded-sm font-bold uppercase tracking-wider" x-text="p.servicio"></span>
                            </td>
                            <td class="p-6 text-center text-xs text-slate-400 font-semibold" x-text="p.fecha_formateada"></td>
                            <td class="p-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="verPaciente(p)" class="p-2.5 bg-slate-100 text-slate-600 rounded-sm hover:bg-blue-50 hover:text-blue-600 transition" title="Ver Detalles">
                                        <i class="fas fa-eye text-sm"></i>
                                    </button>
                                    <button @click="editarPaciente(p)" class="p-2.5 bg-slate-100 text-slate-600 rounded-sm hover:bg-amber-50 hover:text-amber-600 transition" title="Editar Registro">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button @click="confirmarEliminar(p)" class="p-2.5 bg-slate-100 text-slate-600 rounded-sm hover:bg-red-50 hover:text-red-600 transition" title="Eliminar Paciente">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                    <a :href="'/pacientes/' + p.id + '/pdf'" target="_blank" class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2.5 rounded-sm text-xs font-bold hover:bg-red-600 transition shadow-md">
                                        <i class="fas fa-print"></i> PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-if="pacientes.length === 0">
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400 italic font-medium">
                                No se han cargado admisiones o ingresos de pacientes el día de hoy.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL 1: VISUALIZAR DATOS --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4" x-show="modalVer" x-cloak x-transition>
        <div class="bg-white rounded-sm max-w-xl w-full border border-slate-100 overflow-hidden shadow-2xl" @click.away="modalVer = false">
            <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Expediente Clínico Informativo</h3>
                <button @click="modalVer = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Cédula</span>
                        <p class="font-bold text-slate-900 text-sm mt-1" x-text="pacienteSeleccionado.cedula"></p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Edad</span>
                        <p class="font-bold text-slate-900 text-sm mt-1" x-text="pacienteSeleccionado.edad + ' Años'"></p>
                    </div>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Nombre y Apellido</span>
                    <p class="font-bold text-slate-900 text-base mt-1" x-text="pacienteSeleccionado.nombre"></p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Servicio Solicitado</span>
                    <p class="font-bold text-blue-600 text-sm mt-1 uppercase" x-text="pacienteSeleccionado.servicio"></p>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Diagnóstico Inicial (Dx)</span>
                    <p class="text-slate-700 text-sm mt-1 font-medium" x-text="pacienteSeleccionado.diagnostico"></p>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Tratamiento e Insumos</span>
                    <p class="text-slate-700 text-sm mt-1 font-medium bg-slate-50 p-4 rounded-sm whitespace-pre-line" x-text="pacienteSeleccionado.tratamiento || 'Sin especificaciones.'"></p>
                </div>
            </div>
        </div>
    </div>

   {{-- MODAL 2: EDITAR REGISTRO (RESPONSIVE MEJORADO) --}}
<div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4"
     x-show="modalEditar"
     x-cloak
     x-transition>
     
    {{-- Contenedor del modal con altura máxima y flex-column para que header/footer queden fijos --}}
    <div class="bg-white rounded-sm max-w-2xl w-full border border-slate-100 shadow-2xl flex flex-col max-h-[90vh] sm:max-h-[85vh] overflow-hidden"
         @click.away="modalEditar = false">
        
        {{-- Cabecera fija (no se desplaza) --}}
        <div class="flex-shrink-0 p-4 sm:p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Modificar Ficha de Ingreso</h3>
            <button @click="modalEditar = false" class="text-slate-400 hover:text-slate-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Cuerpo del formulario con scroll si el contenido es alto --}}
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 space-y-6">
            <form @submit.prevent="actualizarPaciente()" class="space-y-6">
                {{-- Grid adaptable: 1 columna en móviles, 2 en sm+ --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Cédula</label>
                        <input type="text" x-model="form.cedula"
                               class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Edad</label>
                        <input type="number" x-model="form.edad"
                               class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre y Apellido</label>
                    <input type="text" x-model="form.nombre_apellido"
                           class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Servicio / Área</label>
                    <select x-model="form.area_id"
                            class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Diagnóstico</label>
                    <input type="text" x-model="form.diagnostico"
                           class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tratamiento / Insumos</label>
                    <textarea x-model="form.tratamiento" rows="3"
                              class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 resize-none mt-2"></textarea>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha Ingreso</label>
                    <input type="date" x-model="form.fecha_ingreso"
                           class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                </div>
            </form>
        </div>

      
        <div class="flex-shrink-0 p-4 sm:p-6 border-t border-slate-100 bg-white flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" @click="modalEditar = false"
                    class="w-full sm:w-auto px-5 py-3 sm:px-6 sm:py-4 rounded-sm font-bold text-xs bg-slate-100 text-slate-600 uppercase tracking-wider hover:bg-slate-200 transition">
                Cancelar
            </button>
            <button type="submit"
                    class="w-full sm:w-auto px-6 py-3 sm:px-8 sm:py-4 rounded-sm font-bold text-xs bg-slate-900 text-white uppercase tracking-wider hover:bg-blue-600 transition shadow-lg">
                Guardar Cambios
            </button>
        </div>
    </div>
</div>

    {{-- MODAL 3: CONFIRMAR ELIMINACIÓN --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4" x-show="modalEliminar" x-cloak x-transition>
        <div class="bg-white rounded-sm max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl" @click.away="modalEliminar = false">
            <div class="p-8 text-center space-y-4">
                <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-sm flex items-center justify-center text-xl mx-auto">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">¿Desea eliminar este paciente?</h4>
                    <p class="text-xs text-slate-400 mt-2">Esta acción removerá permanentemente el registro clínico de <span class="font-bold text-slate-700" x-text="pacienteSeleccionado.nombre"></span> del listado de control del día.</p>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4">
                    <button type="button" @click="modalEliminar = false" class="p-4 rounded-sm font-bold text-xs bg-slate-100 text-slate-600 uppercase tracking-wider hover:bg-slate-200 transition">Cancelar</button>
                    <button type="button" @click="eliminarPaciente()" class="p-4 rounded-sm font-bold text-xs bg-rose-600 text-white uppercase tracking-wider hover:bg-rose-700 transition">Confirmar Eliminar</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection