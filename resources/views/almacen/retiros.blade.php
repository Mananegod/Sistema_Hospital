@extends('layouts.app')

@section('title', 'Retiro de Medicamentos')

@section('content')
@include('components.mensajes-notificaciones')
@include('components.loading-overlay')

<div class="max-w-7xl mx-auto" x-data="{
    // Inyección de retiros reales de la base de datos ocurridos hoy
    retiros: [
        @foreach($ultimosRetiros as $retiro)
            { 
                id: {{ $retiro->id }}, 
                nombre: '{{ $retiro->nombre }}', 
                nombre_area: '{{ $retiro->nombre_area }}', 
                cantidad: {{ $retiro->cantidad }}, 
                created_at: '{{ \Carbon\Carbon::parse($retiro->created_at)->format('d/m/Y H:i') }}' 
            },
        @endforeach
    ],
    
    // Inyección de todos los medicamentos disponibles en la base de datos
    medicamentos: [
        @foreach($todosLosMedicamentos as $med)
            { id: {{ $med->id }}, nombre: '{{ $med->nombre_medicamento }}' },
        @endforeach
    ],
    
    // Inyección de las áreas oficiales del hospital desde tu seeder
    areas: [
        @foreach($areas as $area)
            { id: {{ $area->id }}, nombre_area: '{{ $area->nombre_area }}' },
        @endforeach
    ],
    
    // Estado del formulario
    form: {
        medicamento_id: '',
        area_id: '',
        cantidad: ''
    },
    
    // Función adaptada para procesar el envío real hacia Laravel
    registrarRetiro() {
        if (!this.form.medicamento_id || !this.form.area_id || !this.form.cantidad) {
            if (this.$store.toast) this.$store.toast.add('Por favor llena todos los campos.', 'error');
            return;
        }
        
        // Activación del componente loading global
        if (this.$store.loading) {
            this.$store.loading.activate('Procesando retiro...');
        }

        // Construcción dinámica de un formulario HTML nativo para POST
        let formSubmit = document.createElement('form');
        formSubmit.method = 'POST';
        formSubmit.action = '{{ route('almacen.retiros.store') }}';

        // Token de seguridad de Laravel (CSRF)
        let csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        formSubmit.appendChild(csrfInput);

        // Adjuntar campos reactivos de Alpine al formulario final
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
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Retiro de Insumos</h1>
        <p class="text-slate-500 mt-1">Registre la salida de medicamentos de las áreas correspondientes</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Formulario de Retiro --}}
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm sticky top-6">
                <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                    <i class="fas fa-minus-circle text-red-500"></i> Registrar Salida
                </h2>
                
                {{-- Escuchamos el submit --}}
                <form @submit.prevent="registrarRetiro()">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Medicamento</label>
                            <select x-model="form.medicamento_id" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="" disabled selected>Seleccione medicamento</option>
                                <template x-for="med in medicamentos" :key="med.id">
                                    <option :value="med.id" x-text="med.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Área de Retiro</label>
                            <select x-model="form.area_id" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="" disabled selected>Seleccione área</option>
                                <template x-for="area in areas" :key="area.id">
                                    <option :value="area.id" x-text="area.nombre_area"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Cantidad a Retirar</label>
                            <input type="number" x-model="form.cantidad" min="1" placeholder="Ej. 10" 
                                class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <button type="submit" 
                            class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-xl shadow-lg hover:bg-blue-600 transition duration-300">
                            Confirmar Retiro
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800">Retiros de Hoy</h2>
                </div>
                
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Medicamento</th>
                            <th class="px-6 py-4">Área</th>
                            <th class="px-6 py-4 text-center">Cant.</th>
                            <th class="px-6 py-4 text-center">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="retiro in retiros" :key="retiro.id">
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5 font-bold text-slate-800" x-text="retiro.nombre"></td>
                                <td class="px-6 py-5 text-slate-600 text-sm" x-text="retiro.nombre_area"></td>
                                <td class="px-6 py-5 text-center">
                                    <span class="bg-gray-50 text-black-600 px-2 py-1 rounded-lg font-bold" x-text="retiro.cantidad">
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center text-xs text-slate-400" x-text="retiro.created_at"></td>
                            </tr>
                        </template>
                        
                        <template x-if="retiros.length === 0">
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic">No se han registrado retiros hoy.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection