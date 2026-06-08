@extends('layouts.app', ['showSidebar' => false])

@section('title', 'Iniciar Sesión - Hospital TG')

@section('content')
<div class="min-h-screen w-full flex flex-col lg:flex-row bg-white lg:bg-slate-50">
    
    
    <div class="hidden lg:flex w-full lg:w-1/2 flex-col items-center justify-center p-8 lg:p-12 bg-slate-900 border-r border-slate-800 relative overflow-hidden">
        <div class="absolute inset-0 opacity-5 pointer-events-none bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:16px_16px]"></div>
        
        <div class="w-full max-w-md text-center space-y-8 relative z-10">
            <div class="inline-flex bg-blue-600 p-6 rounded-2xl shadow-xl shadow-blue-500/20 transform hover:scale-105 transition-transform duration-300">
                <i class="fa-solid fa-hospital text-white text-5xl"></i>
            </div>

            <div class="space-y-4">
                <h2 class="text-3xl xl:text-4xl font-black text-white tracking-tight uppercase">
                    HOSPITAL <span class="text-blue-500">TG</span>
                </h2>
                <div class="w-16 h-1 bg-blue-600 mx-auto rounded-full"></div>
                <p class="text-slate-400 text-xs xl:text-sm font-bold uppercase tracking-widest">
                    Sistema Integrado de Gestión
                </p>
                <p class="text-slate-400 text-sm xl:text-base leading-relaxed px-4 font-medium">
                    Acceso centralizado para el control de inventario de medicamentos, registros de pacientes y el módulo de vigilancia epidemiológica.
                </p>
            </div>

            {{-- <div class="inline-flex items-center gap-2 bg-slate-950/50 px-4 py-2.5 rounded-xl border border-slate-800">
                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Servidor Local (Offline)</span>
            </div> --}}
        </div>
    </div>

    {{-- COLUMNA DERECHA: Formulario Responsivo --}}
    <div class="w-full lg:w-1/2 flex flex-col justify-between items-center min-h-screen p-6 sm:p-8 lg:p-12 bg-white">
        
        <div class="w-full flex-1 flex flex-col justify-center max-w-md mx-auto py-8">
            
            {{-- Logotipo Móvil --}}
            <div class="text-center lg:hidden mb-8">
                <div class="inline-flex bg-blue-600 p-4 rounded-2xl shadow-md mb-3">
                    <i class="fa-solid fa-hospital text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">HOSPITAL <span class="text-blue-500">TG</span></h1>
            </div>

            {{-- Alerta de Seguridad --}}
            <div class="bg-slate-50 border-l-4 border-amber-500 rounded-r-xl p-4 flex gap-3 shadow-sm mb-8">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5 shrink-0"></i>
                <div class="text-xs sm:text-sm text-slate-600 leading-relaxed font-medium">
                    <span class="font-bold block text-slate-900 mb-1 uppercase tracking-wide text-xs">Control de Acceso Seguro</span>
                    Asegúrese de estar ingresando desde un terminal autorizado del hospital. Toda actividad será auditada.
                </div>
            </div>

            {{-- Títulos --}}
            <div class="mb-8 text-center sm:text-left">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight uppercase">Inicia Sesión</h2>
                <p class="text-slate-400 text-xs sm:text-sm font-bold uppercase tracking-wider mt-2">Introduce tus credenciales para acceder</p>
            </div>

            {{-- Formulario Principal --}}
            <form action="{{ url('/login') }}" method="POST" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-sm font-semibold shadow-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Campo: Usuario --}}
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2 ml-1">Usuario / Nombre</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3.5 text-base font-semibold shadow-inner focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition placeholder:text-slate-300 placeholder:font-normal uppercase">
                </div>

                {{-- Campo: Contraseña --}}
                <div x-data="{ show: false }" class="relative">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2 ml-1">Contraseña de Seguridad</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl px-4 py-3.5 text-base font-semibold shadow-inner focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition pr-12">
                        
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-solid text-lg" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Botón de Enviar --}}
                <div class="pt-4">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/20 transition duration-200 active:scale-95 text-sm sm:text-base uppercase tracking-widest">
                        Acceder al Sistema
                    </button>
                </div>
            </form>

            {{-- Ayuda --}}
            <div class="text-center mt-8">
                <span class="text-xs sm:text-sm text-slate-500 font-medium leading-relaxed block">
                    Si presenta problemas con sus credenciales, comuníquese con el <br class="hidden sm:block"> <span class="text-slate-700 font-bold">Administrador del Sistema</span>.
                </span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="w-full text-center pb-2 pt-6">
            <p class="text-[10px] sm:text-xs font-black text-slate-400 uppercase tracking-widest">
                Hospital Dr. Tiburcio Garrido · Chivacoa, Yaracuy
            </p>
        </div>
    </div>

</div>
@endsection