@extends('layouts.app')

@section('title', 'Retiro de Medicamentos')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="{
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
        medicamentos: [
            @foreach($todosLosMedicamentos as $med)
                { id: {{ $med->id }}, nombre: '{{ $med->nombre_medicamento }}' },
            @endforeach
        ],
        areas: [
            @foreach($areas as $area)
                { id: {{ $area->id }}, nombre_area: '{{ $area->nombre_area }}' },
            @endforeach
        ],
        form: {
            medicamento_id: '',
            area_id: '',
            cantidad: ''
        },
        registrarRetiro() {
            if (!this.form.medicamento_id || !this.form.area_id || !this.form.cantidad) {
                if (this.$store.toast) this.$store.toast.add('Por favor llena todos los campos.', 'error');
                return;
            }
            if (this.$store.loading) {
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
                <div class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm sticky top-6">
                    <h2 class="text-lg font-bold mb-5 flex items-center gap-2">
                        <i class="fas fa-minus-circle text-red-500"></i> Registrar Salida
                    </h2>
                    <form @submit.prevent="registrarRetiro()">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Medicamento</label>
                                <select x-model="form.medicamento_id"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Seleccione medicamento</option>
                                    <template x-for="med in medicamentos" :key="med.id">
                                        <option :value="med.id" x-text="med.nombre"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Área de Retiro</label>
                                <select x-model="form.area_id"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="" disabled selected>Seleccione área</option>
                                    <template x-for="area in areas" :key="area.id">
                                        <option :value="area.id" x-text="area.nombre_area"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Cantidad a Retirar</label>
                                <input type="number" x-model="form.cantidad" min="1" placeholder="Ej. 10"
                                    class="w-full bg-slate-50 border-0 rounded-sm px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit"
                                class="w-full bg-slate-900 text-white font-bold py-3 px-4 rounded-sm shadow-lg hover:bg-blue-600 transition duration-300">
                                Confirmar Retiro
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
                                    <th class="px-6 py-4 whitespace-nowrap">Medicamento</th>
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
                                {{-- Mensaje cuando no hay retiros --}}
                                <template x-if="retiros.length === 0">
                                    <tr>
                                        <td colspan="4"
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
    </div>
@endsection