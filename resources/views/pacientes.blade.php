@extends('layouts.app')

@section('title', 'Pacientes Internados')

@section('content')
    <div class="max-w-7xl mx-auto" x-data='{
        pacientes: @json($pacientes),
        search: "",
        orden: "recientes",
        fecha_inicio: "",
        fecha_fin: "",

        get pacientesFiltrados() {
            let resultado = this.pacientes;

            // 1. Filtrar por texto (nombre, cédula, pasaporte o tutor)
            if (this.search && this.search.trim() !== "") {
                const query = this.search.toLowerCase().trim();
                resultado = resultado.filter(p => {
                    const nombre = (p.nombre || p.nombre_apellido || "").toLowerCase();
                    const cedula = (p.cedula || "").toLowerCase();
                    const cedulaTutor = (p.cedula_tutor || "").toLowerCase();
                    const etiqueta = this.etiquetaDocumento(p.tipo_documento, p.cedula, p.cedula_tutor).toLowerCase();
                    
                    return nombre.includes(query) || 
                           cedula.includes(query) || 
                           cedulaTutor.includes(query) || 
                           etiqueta.includes(query);
                });
            }

            // 2. Filtrar por Rango de Fechas (fecha de ingreso)
            if (this.fecha_inicio) {
                resultado = resultado.filter(p => {
                    if (!p.fecha_ingreso) return false;
                    return p.fecha_ingreso >= this.fecha_inicio;
                });
            }

            if (this.fecha_fin) {
                resultado = resultado.filter(p => {
                    if (!p.fecha_ingreso) return false;
                    return p.fecha_ingreso <= this.fecha_fin;
                });
            }

            // 3. Ordenar resultados
            return resultado.sort((a, b) => {
                if (this.orden === "recientes") {
                    return new Date(b.created_at || b.fecha_ingreso) - new Date(a.created_at || a.fecha_ingreso);
                } else if (this.orden === "antiguos") {
                    return new Date(a.created_at || a.fecha_ingreso) - new Date(b.created_at || b.fecha_ingreso);
                } else if (this.orden === "nombre_asc") {
                    let nomA = (a.nombre || a.nombre_apellido || "").toLowerCase();
                    let nomB = (b.nombre || b.nombre_apellido || "").toLowerCase();
                    return nomA.localeCompare(nomB);
                } else if (this.orden === "nombre_desc") {
                    let nomA = (a.nombre || a.nombre_apellido || "").toLowerCase();
                    let nomB = (b.nombre || b.nombre_apellido || "").toLowerCase();
                    return nomB.localeCompare(nomA);
                }
                return 0;
            });
        },

        formCreate: {
            tipo_documento: "V",
            cedula: "",
            cedula_tutor: "",
            nombre_apellido: "",
            fecha_nacimiento: "",
            edad: "",
            genero: "",
            area_id: "",
            diagnostico: "",
            tratamiento: "",
            fecha_ingreso: "{{ date("Y-m-d") }}"
        },

        editForm: {
            id: null,
            tipo_documento: "V",
            cedula: "",
            cedula_tutor: "",
            nombre_apellido: "",
            fecha_nacimiento: "",
            edad: "",
            genero: "",
            area_id: "",
            diagnostico: "",
            tratamiento: "",
            fecha_ingreso: ""
        },

        modalVer: false,
        modalEditar: false,
        modalEliminar: false,
        modalDuplicado: false,
        modalAlerta: false,
        mensajeAlerta: "",
        pacienteDuplicadoInfo: null,

        pacienteSeleccionado: {},
        consultaDocumento: null,
        posiblesCoincidencias: [],
        consultandoDocumento: false,

        formatearCedula(num) {
            if (!num) return "";
            let limpio = String(num).replace(/\D/g, "");
            return limpio.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },

        calcularEdad(fechaStr) {
            if (!fechaStr) return "";
            const hoy = new Date();
            const nacimiento = new Date(fechaStr);
            
            let anos = hoy.getFullYear() - nacimiento.getFullYear();
            let meses = hoy.getMonth() - nacimiento.getMonth();
            let dias = hoy.getDate() - nacimiento.getDate();

            if (dias < 0) {
                meses--;
                const ultimoMes = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
                dias += ultimoMes.getDate();
            }

            if (meses < 0) {
                anos--;
                meses += 12;
            }

            if (anos > 0) {
                return anos + " años";
            } else if (meses > 0) {
                return meses + (meses === 1 ? " mes" : " meses");
            } else {
                return (dias > 0 ? dias : 0) + (dias === 1 ? " día" : " días");
            }
        },

        etiquetaDocumento(tipo, cedula, cedulaTutor) {
            if (tipo === "SC") {
                return cedulaTutor ? "SIN CÉDULA (Tutor: " + this.formatearCedula(cedulaTutor) + ")" : "SIN CÉDULA / TUTOR";
            }
            if (!cedula) return "SIN CÉDULA / TUTOR";
            
            if (["V", "E"].includes(tipo)) {
                return tipo + "-" + this.formatearCedula(cedula);
            }
            return (tipo || "V") + "-" + cedula;
        },
        
        mostrarAlerta(mensaje) {
            this.mensajeAlerta = mensaje;
            this.modalAlerta = true;
        },

        async verificarPaciente(esEdicion = false) {
            const f = esEdicion ? this.editForm : this.formCreate;
            const tipo = f.tipo_documento;
            const documento = (f.cedula || "").trim().toUpperCase();
            
            if (!esEdicion) {
                this.consultaDocumento = null;
                this.posiblesCoincidencias = [];
            }

            if (tipo === "SC") return;
            if (!documento || documento.length < 4) return;

            this.consultandoDocumento = true;
            try {
                const params = new URLSearchParams({
                    tipo_documento: tipo,
                    cedula: documento,
                    nombre_apellido: f.nombre_apellido || "",
                    fecha_nacimiento: f.fecha_nacimiento || ""
                });
                const response = await fetch("/pacientes/verificar-documento?" + params.toString(), {
                    headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" }
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || "No se pudo verificar el documento.");
                
                if (!esEdicion) {
                    this.consultaDocumento = data;
                    this.posiblesCoincidencias = data.posibles || [];

                    const esCedulaCompleta = (tipo === "P" && /^[A-Z0-9]{5,20}$/.test(documento)) ||
                                             (["V","E"].includes(tipo) && /^[0-9]{6,10}$/.test(documento));

                    if (data.encontrado && data.paciente && esCedulaCompleta) {
                        this.pacienteDuplicadoInfo = data.paciente;
                        this.modalDuplicado = true;
                    }
                }
                return data;
            } catch (error) {
                console.error(error);
            } finally {
                this.consultandoDocumento = false;
            }
        },

        async registrarPaciente() {
            this.formCreate.edad = this.calcularEdad(this.formCreate.fecha_nacimiento);

            if (!this.formCreate.nombre_apellido || !this.formCreate.fecha_nacimiento || !this.formCreate.genero || !this.formCreate.area_id || !this.formCreate.diagnostico) {
                this.mostrarAlerta("Por favor complete todos los campos obligatorios.");
                return;
            }

            if (this.formCreate.tipo_documento === "SC") {
                let diffAnos = new Date().getFullYear() - new Date(this.formCreate.fecha_nacimiento).getFullYear();
                if (diffAnos > 12) {
                    this.mostrarAlerta("La opción Sin Cédula está destinada exclusivamente a niños menores de 13 años.");
                    return;
                }
                if (!this.formCreate.cedula_tutor || !/^[0-9]{6,10}$/.test(this.formCreate.cedula_tutor)) {
                    this.mostrarAlerta("Para registrar un menor sin cédula, debe indicar obligatoriamente una cédula de tutor válida (entre 6 y 10 dígitos numéricos).");
                    return;
                }
            } else {
                let diffAnos = new Date().getFullYear() - new Date(this.formCreate.fecha_nacimiento).getFullYear();
                if (diffAnos < 10 && this.formCreate.tipo_documento !== "P") {
                    this.mostrarAlerta("Al ser un paciente menor de 10 años, debe seleccionar la opción \"Sin cédula / Usar Tutor (Menor)\" e indicar la cédula de su tutor.");
                    return;
                }

                if (this.formCreate.tipo_documento === "P") {
                    if (!/^[A-Za-z0-9]{5,20}$/.test(this.formCreate.cedula)) {
                        this.mostrarAlerta("El pasaporte debe contener entre 5 y 20 caracteres alfanuméricos.");
                        return;
                    }
                } else if (!/^[0-9]{6,10}$/.test(this.formCreate.cedula)) {
                    this.mostrarAlerta("La cédula debe contener entre 6 y 10 dígitos.");
                    return;
                }
            }

            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(this.formCreate.nombre_apellido)) {
                this.mostrarAlerta("El nombre y apellido debe contener exclusivamente letras.");
                return;
            }

            let añoIngreso = new Date(this.formCreate.fecha_ingreso).getFullYear();
            if (añoIngreso > 2036) {
                this.mostrarAlerta("La fecha de ingreso no puede ser posterior al año 2036.");
                return;
            }

            if (this.formCreate.tipo_documento !== "SC" && this.formCreate.cedula) {
                const verificacion = await this.verificarPaciente(false);
                if (verificacion && verificacion.encontrado) {
                    this.pacienteDuplicadoInfo = verificacion.paciente;
                    this.modalDuplicado = true;
                    return;
                }
            }

            if (this.$store && this.$store.loading) this.$store.loading.activate("Procesando solicitud...");

            try {
                let formData = new FormData();
                formData.append("_token", "{{ csrf_token() }}");
                for (let key in this.formCreate) {
                    if (this.formCreate[key] !== null && this.formCreate[key] !== undefined) {
                        formData.append(key, this.formCreate[key]);
                    }
                }

                const response = await fetch("{{ route('pacientes.store') }}", {
                    method: "POST",
                    headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    if (result.paciente_duplicado) {
                        this.pacienteDuplicadoInfo = result.paciente_duplicado;
                        this.modalDuplicado = true;
                    } else {
                        this.mostrarAlerta(result.message || "No es posible registrar al paciente con los datos suministrados.");
                    }
                    if (this.$store && this.$store.loading) this.$store.loading.deactivate();
                    return;
                }

                window.location.reload();
            } catch (e) {
                console.error(e);
                if (this.$store && this.$store.loading) this.$store.loading.deactivate();
                this.mostrarAlerta("Ocurrió un error al procesar la solicitud.");
            }
        },

        editarPaciente(paciente) {
            this.editForm.id = paciente.id;
            this.editForm.tipo_documento = paciente.tipo_documento || "V";
            this.editForm.cedula = paciente.cedula || "";
            this.editForm.cedula_tutor = paciente.cedula_tutor || "";
            this.editForm.nombre_apellido = paciente.nombre_apellido || paciente.nombre || "";
            this.editForm.fecha_nacimiento = paciente.fecha_nacimiento || "";
            this.editForm.edad = paciente.edad;
            this.editForm.genero = paciente.genero;
            this.editForm.area_id = paciente.area_id;
            this.editForm.diagnostico = paciente.diagnostico;
            this.editForm.tratamiento = paciente.tratamiento;
            this.editForm.fecha_ingreso = paciente.fecha_ingreso;
            this.modalEditar = true;
        },

        actualizarPaciente() {
            this.editForm.edad = this.calcularEdad(this.editForm.fecha_nacimiento);

            if (!this.editForm.genero || !this.editForm.fecha_nacimiento) {
                this.mostrarAlerta("Debe completar el género y la fecha de nacimiento.");
                return;
            }

            if (this.editForm.tipo_documento === "SC") {
                let diffAnos = new Date().getFullYear() - new Date(this.editForm.fecha_nacimiento).getFullYear();
                if (diffAnos > 12) {
                    this.mostrarAlerta("La opción Sin Cédula está destinada a niños menores de 13 años.");
                    return;
                }
                if (!this.editForm.cedula_tutor || !/^[0-9]{6,10}$/.test(this.editForm.cedula_tutor)) {
                    this.mostrarAlerta("Para registrar un menor sin cédula, debe indicar obligatoriamente una cédula de tutor válida (entre 6 y 10 dígitos numéricos).");
                    return;
                }
            } else {
                let diffAnos = new Date().getFullYear() - new Date(this.editForm.fecha_nacimiento).getFullYear();
                if (diffAnos < 10 && this.editForm.tipo_documento !== "P") {
                    this.mostrarAlerta("Al ser un paciente menor de 10 años, debe seleccionar la opción \"Sin cédula / Usar Tutor (Menor)\" e indicar la cédula de su tutor.");
                    return;
                }

                if (this.editForm.tipo_documento === "P") {
                    if (!/^[A-Za-z0-9]{5,20}$/.test(this.editForm.cedula)) {
                        this.mostrarAlerta("El pasaporte debe contener entre 5 y 20 caracteres alfanuméricos.");
                        return;
                    }
                } else if (!/^[0-9]{6,10}$/.test(this.editForm.cedula)) {
                    this.mostrarAlerta("La cédula debe contener entre 6 y 10 dígitos.");
                    return;
                }
            }

            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(this.editForm.nombre_apellido)) {
                this.mostrarAlerta("El nombre y apellido debe contener exclusivamente letras.");
                return;
            }

            let añoIngreso = new Date(this.editForm.fecha_ingreso).getFullYear();
            if (añoIngreso > 2036) {
                this.mostrarAlerta("La fecha de ingreso no puede ser posterior al año 2036.");
                return;
            }

            this.submitForm("/pacientes/" + this.editForm.id + "/update", this.editForm);
        },

        verPaciente(paciente) {
            this.pacienteSeleccionado = paciente;
            this.modalVer = true;
        },

        confirmarEliminar(paciente) {
            this.pacienteSeleccionado = paciente;
            this.modalEliminar = true;
        },

        eliminarPaciente() {
            this.submitForm("/pacientes/" + this.pacienteSeleccionado.id + "/delete", null);
        },

        submitForm(actionUrl, dataObject) {
            if (this.$store && this.$store.loading) this.$store.loading.activate("Procesando solicitud...");

            let formSubmit = document.createElement("form");
            formSubmit.method = "POST";
            formSubmit.action = actionUrl;

            let csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value = "{{ csrf_token() }}";
            formSubmit.appendChild(csrfInput);

            if (dataObject) {
                for (let key in dataObject) {
                    if (dataObject[key] !== null && dataObject[key] !== undefined) {
                        let input = document.createElement("input");
                        input.type = "hidden";
                        input.name = key;
                        input.value = dataObject[key];
                        formSubmit.appendChild(input);
                    }
                }
            }

            document.body.appendChild(formSubmit);
            formSubmit.submit();
        }
    }'>

        {{-- Encabezado --}}
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Tipo de identificación</label>
                        <select x-model="formCreate.tipo_documento" @change="formCreate.cedula=''; formCreate.cedula_tutor=''; consultaDocumento=null; posiblesCoincidencias=[]"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                            <option value="V">V - Venezolano</option>
                            <option value="E">E - Extranjero</option>
                            <option value="P">Pasaporte</option>
                            <option value="SC">Sin cédula / Usar Tutor (Menor)</option>
                        </select>

                        <!-- Input para Cédula / Pasaporte Normal -->
                        <input x-show="formCreate.tipo_documento !== 'SC'" type="text" x-model="formCreate.cedula"
                            @input.debounce.300ms="verificarPaciente()" @blur="verificarPaciente()"
                            :placeholder="formCreate.tipo_documento === 'P' ? 'Número de pasaporte' : 'Ej: 24123456'"
                            :maxlength="formCreate.tipo_documento === 'P' ? 20 : 10"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">

                        <!-- Input separado para el Tutor cuando es Menor sin cédula (SC) -->
                        <input x-show="formCreate.tipo_documento === 'SC'" type="text" x-model="formCreate.cedula_tutor"
                            placeholder="Cédula del Tutor Legal (Ej: 24123456)"
                            maxlength="10"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">

                        <p x-show="formCreate.tipo_documento === 'SC'" class="text-xs text-amber-700 mt-2">Permite registrar varios niños bajo la cédula de un mismo representante/tutor legal sin conflictos.</p>
                        <p x-show="consultandoDocumento" class="text-xs text-slate-400 mt-2">Verificando registro...</p>

                        <!-- Alerta visual directa bajo el campo si ya está registrado -->
                        <div x-show="consultaDocumento && consultaDocumento.encontrado" class="mt-2 p-3 bg-rose-50 text-rose-700 text-xs rounded-sm">
                            <strong>¡Cédula ya registrada!</strong>
                            <p x-text="consultaDocumento?.paciente?.nombre_apellido"></p>
                        </div>

                        <div x-show="posiblesCoincidencias.length" class="mt-2 p-3 bg-amber-50 text-amber-800 text-xs rounded-sm">
                            <strong>Posibles coincidencias de menores registrados:</strong>
                            <template x-for="coincidencia in posiblesCoincidencias" :key="coincidencia.id">
                                <p x-text="coincidencia.nombre_apellido + ' — ' + calcularEdad(coincidencia.fecha_nacimiento)"></p>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Nombre y Apellido</label>
                        <input type="text" x-model="formCreate.nombre_apellido" @input.debounce.500ms="if(formCreate.tipo_documento === 'SC') verificarPaciente()" placeholder="Nombre completo del paciente"
                            oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Fecha de Nacimiento</label>
                        <input type="date" x-model="formCreate.fecha_nacimiento" @change="if(formCreate.tipo_documento === 'SC') verificarPaciente()" :max="'{{ date('Y-m-d') }}'"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                        <p class="text-xs text-blue-600 font-semibold mt-1" x-show="formCreate.fecha_nacimiento">
                            Edad estimada: <span x-text="calcularEdad(formCreate.fecha_nacimiento)"></span>
                        </p>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Género</label>
                        <select x-model="formCreate.genero"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                            <option value="" disabled selected>Seleccione...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Servicio / Área Hospitalaria</label>
                        <select x-model="formCreate.area_id"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                            <option value="" disabled selected>Seleccione el servicio...</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Diagnóstico Inicial (Dx)</label>
                        <input type="text" x-model="formCreate.diagnostico" placeholder="Indique el diagnóstico clínico..."
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-3">
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Tratamiento Médico / Descripción de Insumos</label>
                        <textarea x-model="formCreate.tratamiento" rows="2"
                            placeholder="Escriba detalladamente el tratamiento o insumos..."
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 resize-none mt-2"></textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 px-1 uppercase tracking-widest">Fecha Ingreso</label>
                        <input type="date" x-model="formCreate.fecha_ingreso"
                            class="w-full bg-slate-50 p-4 rounded-sm border-none text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="bg-slate-900 text-white px-10 py-5 rounded-sm font-bold text-xs uppercase tracking-[0.2em] hover:bg-blue-600 transition duration-300 shadow-xl shadow-slate-900/10">
                        Registrar e Internar Paciente
                    </button>
                </div>
            </form>
        </div>

        {{-- Tabla de Pacientes Registrados --}}
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-50 flex flex-col gap-4 bg-slate-50/50">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <div class="w-8 h-8 rounded-sm bg-blue-600 text-white flex items-center justify-center text-xs">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Listado General de Pacientes Registrados</h3>
                    </div>

                    <!-- ORDENAMIENTO -->
                    <div class="w-full md:w-52">
                        <select x-model="orden"
                            class="w-full bg-white px-3 py-2.5 rounded-sm border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm cursor-pointer">
                            <option value="recientes">Más Recientes (Ingreso)</option>
                            <option value="antiguos">Más Antiguos (Ingreso)</option>
                            <option value="nombre_asc">Nombre (A - Z)</option>
                            <option value="nombre_desc">Nombre (Z - A)</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN DE FILTROS AVANZADOS (BUSCADOR + RANGO DE FECHAS) -->
                <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-12 gap-3 pt-2 border-t border-slate-200/60">
                    <!-- Búsqueda por texto/cédula -->
                    <div class="relative md:col-span-4">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" x-model="search" placeholder="Buscar por nombre, cédula o tutor..."
                            class="w-full bg-white pl-10 pr-8 py-2.5 rounded-sm border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm">
                        <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    <!-- Fecha Desde -->
                    <div class="flex items-center gap-2 md:col-span-3">
                        <span class="text-[10px] font-bold uppercase text-slate-400 whitespace-nowrap">Desde:</span>
                        <input type="date" x-model="fecha_inicio"
                            class="w-full bg-white px-3 py-2 rounded-sm border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 transition shadow-sm">
                    </div>

                    <!-- Fecha Hasta -->
                    <div class="flex items-center gap-2 md:col-span-3">
                        <span class="text-[10px] font-bold uppercase text-slate-400 whitespace-nowrap">Hasta:</span>
                        <input type="date" x-model="fecha_fin"
                            class="w-full bg-white px-3 py-2 rounded-sm border border-slate-200 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 transition shadow-sm">
                    </div>

                    <!-- Botón Limpiar Filtros de Fecha/Búsqueda -->
                    <div class="md:col-span-2 flex items-center">
                        <button type="button" @click="search = ''; fecha_inicio = ''; fecha_fin = ''"
                            x-show="search.length > 0 || fecha_inicio !== '' || fecha_fin !== ''"
                            class="w-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 font-bold text-[10px] uppercase tracking-wider py-2.5 px-3 rounded-sm transition flex items-center justify-center gap-1.5 shadow-xs">
                            <i class="fas fa-filter-circle-xmark"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-bold uppercase tracking-widest text-slate-400 bg-slate-50/20">
                            <th class="p-6">Documento</th>
                            <th class="p-6">Paciente</th>
                            <th class="p-6 text-center">Edad / Género</th>
                            <th class="p-6">Servicio / Área</th>
                            <th class="p-6 text-center">Ingreso</th>
                            <th class="p-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                        <template x-for="p in pacientesFiltrados" :key="p.id">
                            <tr class="hover:bg-slate-50/50 transition duration-150">
                                <td class="p-6 text-slate-400 font-bold" x-text="etiquetaDocumento(p.tipo_documento, p.cedula, p.cedula_tutor)"></td>
                                <td class="p-6 font-bold text-slate-900" x-text="p.nombre"></td>
                                <td class="p-6 text-center" x-text="calcularEdad(p.fecha_nacimiento) + ' (' + p.genero + ')'"></td>
                                <td class="p-6">
                                    <span class="bg-blue-50 text-blue-700 text-xs px-3 py-1.5 rounded-sm font-bold uppercase tracking-wider" x-text="p.servicio"></span>
                                </td>
                                <td class="p-6 text-center text-xs text-slate-400 font-semibold" x-text="p.fecha_formateada"></td>
                                <td class="p-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="verPaciente(p)"
                                            class="p-2.5 bg-slate-100 text-slate-600 rounded-sm hover:bg-blue-50 hover:text-blue-600 transition"
                                            title="Ver Detalles">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button @click="editarPaciente(p)"
                                            class="p-2.5 bg-slate-100 text-slate-600 rounded-sm hover:bg-amber-50 hover:text-amber-600 transition"
                                            title="Editar Registro">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <button @click="confirmarEliminar(p)"
                                            class="p-2.5 bg-slate-100 text-slate-600 rounded-sm hover:bg-red-50 hover:text-red-600 transition"
                                            title="Eliminar Paciente">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                        <a :href="'/pacientes/' + p.id + '/pdf'" target="_blank"
                                            class="inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2.5 rounded-sm text-xs font-bold hover:bg-red-600 transition shadow-md">
                                            <i class="fas fa-print"></i> PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template x-if="pacientesFiltrados.length === 0">
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-400 italic font-medium">
                                    No se encontraron registros de pacientes que coincidan con los filtros de búsqueda o el rango de fechas seleccionado.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL ADVERTENCIA: PACIENTE YA REGISTRADO --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4"
            x-show="modalDuplicado" x-cloak x-transition>
            <div class="bg-white rounded-sm max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl p-6 text-center space-y-4"
                @click.away="modalDuplicado = false">
                <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-sm flex items-center justify-center text-xl mx-auto">
                    <i class="fas fa-id-card text-2xl"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">¡Paciente ya registrado!</h4>
                    <p class="text-xs text-slate-500 mt-2">
                        El número de identificación ingresado ya se encuentra registrado en el sistema bajo el expediente de:
                    </p>
                    <div class="mt-3 p-4 bg-slate-50 rounded-sm text-left text-xs space-y-1.5 font-medium text-slate-700">
                        <p><strong>Nombre:</strong> <span x-text="pacienteDuplicadoInfo?.nombre_apellido"></span></p>
                        <p><strong>Documento:</strong> <span x-text="etiquetaDocumento(pacienteDuplicadoInfo?.tipo_documento, pacienteDuplicadoInfo?.cedula, pacienteDuplicadoInfo?.cedula_tutor)"></span></p>
                        <p><strong>Edad:</strong> <span x-text="calcularEdad(pacienteDuplicadoInfo?.fecha_nacimiento)"></span></p>
                        <p><strong>Servicio:</strong> <span x-text="pacienteDuplicadoInfo?.servicio || 'No especificado'"></span></p>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="button" @click="modalDuplicado = false"
                        class="w-full p-4 rounded-sm font-bold text-xs bg-slate-900 text-white uppercase tracking-wider hover:bg-blue-600 transition">
                        Entendido, verificar datos
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL DE ALERTA GENERAL --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4"
            x-show="modalAlerta" x-cloak x-transition>
            <div class="bg-white rounded-sm max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl p-6 text-center space-y-4"
                @click.away="modalAlerta = false">
                <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-sm flex items-center justify-center text-xl mx-auto">
                    <i class="fas fa-exclamation-circle text-2xl"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-slate-900">Aviso del Sistema</h4>
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed" x-text="mensajeAlerta"></p>
                </div>
                <div class="pt-2">
                    <button type="button" @click="modalAlerta = false"
                        class="w-full p-4 rounded-sm font-bold text-xs bg-slate-900 text-white uppercase tracking-wider hover:bg-blue-600 transition">
                        Entendido
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL 1: VISUALIZAR DATOS --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4"
            x-show="modalVer" x-cloak x-transition>
            <div class="bg-white rounded-sm max-w-xl w-full border border-slate-100 overflow-hidden shadow-2xl"
                @click.away="modalVer = false">
                <div class="p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Expediente Clínico Informativo</h3>
                    <button @click="modalVer = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase block">Documento</span>
                            <p class="font-bold text-slate-900 text-sm mt-1" x-text="etiquetaDocumento(pacienteSeleccionado.tipo_documento, pacienteSeleccionado.cedula, pacienteSeleccionado.cedula_tutor)"></p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase block">Edad</span>
                            <p class="font-bold text-slate-900 text-sm mt-1" x-text="calcularEdad(pacienteSeleccionado.fecha_nacimiento)"></p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase block">Género</span>
                            <p class="font-bold text-slate-900 text-sm mt-1" x-text="pacienteSeleccionado.genero"></p>
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
                        <p class="text-slate-700 text-sm mt-1 font-medium bg-slate-50 p-4 rounded-sm whitespace-pre-line"
                            x-text="pacienteSeleccionado.tratamiento || 'Sin especificaciones.'"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL 2: EDITAR REGISTRO --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4"
            x-show="modalEditar" x-cloak x-transition>
            <div class="bg-white rounded-sm max-w-2xl w-full border border-slate-100 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
                @click.away="modalEditar = false">
                <div class="flex-shrink-0 p-4 sm:p-6 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Modificar Ficha de Ingreso</h3>
                    <button @click="modalEditar = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                </div>
                <form @submit.prevent="actualizarPaciente()" class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipo de identificación</label>
                            <select x-model="editForm.tipo_documento" @change="editForm.cedula=''; editForm.cedula_tutor=''"
                                class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                                <option value="V">V - Venezolano</option>
                                <option value="E">E - Extranjero</option>
                                <option value="P">Pasaporte</option>
                                <option value="SC">Sin cédula / Tutor</option>
                            </select>
                            <input x-show="editForm.tipo_documento !== 'SC'" type="text" x-model="editForm.cedula"
                                :placeholder="editForm.tipo_documento === 'P' ? 'Número de pasaporte' : 'Número de cédula'"
                                :maxlength="editForm.tipo_documento === 'P' ? 20 : 10"
                                class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                            <input x-show="editForm.tipo_documento === 'SC'" type="text" x-model="editForm.cedula_tutor"
                                placeholder="Cédula Tutor"
                                maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha de Nacimiento</label>
                            <input type="date" x-model="editForm.fecha_nacimiento" :max="'{{ date('Y-m-d') }}'"
                                class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                            <p class="text-xs text-blue-600 font-semibold mt-1" x-show="editForm.fecha_nacimiento">
                                Edad: <span x-text="calcularEdad(editForm.fecha_nacimiento)"></span>
                            </p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Género</label>
                            <select x-model="editForm.genero"
                                class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nombre y Apellido</label>
                        <input type="text" x-model="editForm.nombre_apellido"
                            oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                            class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Servicio / Área</label>
                        <select x-model="editForm.area_id"
                            class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->nombre_area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Diagnóstico</label>
                        <input type="text" x-model="editForm.diagnostico"
                            class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tratamiento / Insumos</label>
                        <textarea x-model="editForm.tratamiento" rows="3"
                            class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 resize-none mt-2"></textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fecha Ingreso</label>
                        <input type="date" x-model="editForm.fecha_ingreso"
                            class="w-full bg-slate-50 p-3 sm:p-4 rounded-sm border-none text-xs sm:text-sm font-semibold outline-none focus:ring-2 focus:ring-blue-500/20 mt-2">
                    </div>
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="modalEditar = false"
                            class="w-full sm:w-auto px-5 py-3 rounded-sm font-bold text-xs bg-slate-100 text-slate-600 uppercase tracking-wider hover:bg-slate-200 transition">Cancelar</button>
                        <button type="submit"
                            class="w-full sm:w-auto px-6 py-3 rounded-sm font-bold text-xs bg-slate-900 text-white uppercase tracking-wider hover:bg-blue-600 transition shadow-lg">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: CONFIRMAR ELIMINACIÓN --}}
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4"
            x-show="modalEliminar" x-cloak x-transition>
            <div class="bg-white rounded-sm max-w-md w-full border border-slate-100 overflow-hidden shadow-2xl"
                @click.away="modalEliminar = false">
                <div class="p-8 text-center space-y-4">
                    <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-sm flex items-center justify-center text-xl mx-auto">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">¿Desea eliminar este paciente?</h4>
                        <p class="text-xs text-slate-400 mt-2">Esta acción removerá permanentemente el registro clínico de
                            <span class="font-bold text-slate-700" x-text="pacienteSeleccionado.nombre"></span> del listado de control.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-4">
                        <button type="button" @click="modalEliminar = false"
                            class="p-4 rounded-sm font-bold text-xs bg-slate-100 text-slate-600 uppercase tracking-wider hover:bg-slate-200 transition">Cancelar</button>
                        <button type="button" @click="eliminarPaciente()"
                            class="p-4 rounded-sm font-bold text-xs bg-rose-600 text-white uppercase tracking-wider hover:bg-rose-700 transition">Confirmar Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection