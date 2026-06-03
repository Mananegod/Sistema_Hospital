@extends('layouts.app', ['showSidebar' => false])

@section('title', 'Iniciar Sesión - Hospital TG')

@section('content')
<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 bg-slate-50">
    
    {{-- COLUMNA IZQUIERDA: Identidad Institucional y Gestión Médica --}}
    <div class="hidden lg:flex flex-col items-center justify-center p-12 bg-slate-900 border-r border-slate-800 relative overflow-hidden">
        {{-- Sutil patrón geométrico de fondo para dar profundidad --}}
        <div class="absolute inset-0 opacity-5 pointer-events-none bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="max-w-md text-center space-y-8 relative z-10">
            
            {{-- Escudo / Logotipo Central de Gestión Médica --}}
            <div class="inline-flex bg-blue-600 p-6 rounded-2xl shadow-xl shadow-blue-500/20 transform hover:scale-105 transition-transform duration-300">
                <i class="fa-solid fa-hospital text-white text-4xl"></i>
            </div>

            {{-- Textos Institucionales Estandarizados --}}
            <div class="space-y-4">
                <h2 class="text-3xl font-black text-white tracking-tight uppercase">
                    HOSPITAL <span class="text-blue-500">TG</span>
                </h2>
                <div class="w-16 h-1 bg-blue-600 mx-auto rounded-full"></div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">
                    Sistema Integrado de Gestión
                </p>
                <p class="text-slate-400 text-sm leading-relaxed px-6 font-medium">
                    Acceso centralizado para el control de inventario de medicamentos, registros de pacientes y el módulo de vigilancia epidemiológica.
                </p>
            </div>

            {{-- Indicador de Estado del Servidor Local --}}
            <div class="inline-flex items-center gap-2 bg-slate-950/50 px-4 py-2 rounded-xl border border-slate-800">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Servidor Local Conectado (Offline)</span>
            </div>
        </div>
    </div>

    {{-- COLUMNA DERECHA: Formulario de Login Limpio y Profesional --}}
    <div class="flex flex-col justify-center items-center p-6 sm:p-12 md:p-20 bg-white">
        <div class="w-full max-w-md space-y-8">
            
            {{-- Logotipo visible solo en móviles para no perder la identidad --}}
            <div class="text-center lg:hidden mb-4">
                <div class="inline-flex bg-blue-600 p-3 rounded-xl shadow-md mb-2">
                    <i class="fa-solid fa-hospital text-white text-xl"></i>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></h1>
            </div>

            {{-- Alerta de Seguridad (Diseño Slate/Amber Oficial) --}}
            <div class="bg-slate-50 border-l-4 border-amber-500 rounded-r-xl p-4 flex gap-3 shadow-xs">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
                <div class="text-xs text-slate-600 leading-relaxed font-medium">
                    <span class="font-bold block text-slate-900 mb-0.5 uppercase tracking-wide text-[11px]">Control de Acceso Seguro</span>
                    Asegúrese de estar ingresando desde un terminal autorizado del hospital. Toda actividad dentro de la intranet será auditada en la bitácora.
                </div>
            </div>

            {{-- Título de la sección --}}
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Inicia Sesión</h2>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mt-1">Introduce tus credenciales para acceder</p>
            </div>

            {{-- Formulario Principal --}}
            <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                @csrf

                {{-- Manejo de Alertas de Validación de Laravel --}}
                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-xs font-semibold shadow-xs">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Campo: Usuario --}}
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Usuario / Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3.5 text-sm font-semibold shadow-inner focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition placeholder:text-slate-300 placeholder:font-normal uppercase">
                </div>

                {{-- Campo: Contraseña --}}
                <div x-data="{ show: false }" class="relative">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 ml-1">Contraseña de Seguridad</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3.5 text-sm font-semibold shadow-inner focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition pr-12">
                        
                        {{-- Botón para alternar visibilidad --}}
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Botón de Enviar (Estandarizado en Azul Rey con sombra) --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/20 transition duration-200 active:scale-98 text-xs uppercase tracking-widest">
                        Acceder al Sistema
                    </button>
                </div>
            </form>

            {{-- Links Auxiliares inferiores --}}
            <div class="text-center pt-2">
                <span class="text-xs text-slate-400 font-medium">
                    Si presenta problemas con sus credenciales, comuníquese con el <span class="text-slate-600 font-bold">Administrador del Sistema</span>.
                </span>
            </div>
        </div>

        {{-- Footer Institucional Fijo --}}
        <p class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest mt-12 lg:mt-24">
            Hospital Dr. Tiburcio Garrido · Chivacoa, Yaracuy
        </p>
    </div>

</div>
@endsection