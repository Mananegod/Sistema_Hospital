@extends('layouts.app')

@section('title', 'Inicio - Hospital TG')

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- Header con Botón de Notificaciones --}}
    <div class="flex justify-end mb-6">
        <a href="{{ route('notificaciones.index') }}" 
           class="relative w-12 h-12 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-100 hover:bg-red-50 transition-all shadow-sm group"
           title="Alertas de Inventario">
            <i class="fa-solid fa-bell text-xl"></i>
            {{-- Indicador de Alerta Crítica (Punto rojo animado) --}}
            <span class="absolute top-0 right-0 translate-x-1/3 -translate-y-1/3 w-4 h-4 bg-red-600 border-2 border-white rounded-full animate-pulse shadow-sm"></span>
        </a>
    </div>

    {{-- Header hero --}}
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 md:p-12 mb-12 text-center shadow-xl">
        <span class="inline-block px-4 py-1.5 bg-blue-500/20 border border-blue-500/30 rounded-full text-blue-300 text-xs font-bold uppercase tracking-wider mb-4">
            Panel de Administración
        </span>
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
            Sistema Integral de Gestión
        </h1>
        <h2 class="text-3xl md:text-4xl font-extrabold text-blue-400 mb-4">Hospital Dr. Tiburcio Garrido</h2>
        <p class="text-slate-400 max-w-2xl mx-auto text-base md:text-lg">
            Bienvenido al panel central. Seleccione el módulo correspondiente para gestionar los recursos, personal y estadísticas de la institución.
        </p>
    </div>

    {{-- Tarjetas de módulos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {{-- Inventario --}}
        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="bg-blue-50 text-blue-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-inner">
                <i class="fa-solid fa-boxes-stacked text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Gestión de Almacén</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Control de stock, entradas de insumos médicos y monitoreo de niveles críticos por departamentos.
            </p>
            <a href="{{ route('almacen.index') }}" class="inline-flex items-center text-blue-600 font-bold text-sm hover:gap-2 transition-all uppercase tracking-wider">
                Acceder módulo <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>

        {{-- Retiros --}}
        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="bg-indigo-50 text-indigo-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-inner">
                <i class="fa-solid fa-hand-holding-medical text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Retiros de Insumos</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Registro de egresos de medicamentos y material médico-quirúrgico asignados a pacientes o áreas.
            </p>
            <a href="{{ route('retiros.index') }}" class="inline-flex items-center text-indigo-600 font-bold text-sm hover:gap-2 transition-all uppercase tracking-wider">
                Acceder módulo <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>

        {{-- Personal --}}
        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="bg-purple-50 text-purple-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors shadow-inner">
                <i class="fa-solid fa-user-doctor text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Control de Personal</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Administración de cuentas de usuario, roles y registro de actividad dentro del sistema hospitalario.
            </p>
            <a href="{{ route('personal.index') }}" class="inline-flex items-center text-purple-600 font-bold text-sm hover:gap-2 transition-all uppercase tracking-wider">
                Acceder módulo <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>

        {{-- Pacientes --}}
        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
            <div class="bg-emerald-50 text-emerald-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors shadow-inner">
                <i class="fa-solid fa-hospital-user text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Registro de Pacientes</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Historiales clínicos digitales, ingresos y gestión de camas por departamento médico.
            </p>
            <a href="{{ route('pacientes.index') }}" class="inline-flex items-center text-emerald-600 font-bold text-sm hover:gap-2 transition-all uppercase tracking-wider">
                Acceder módulo <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>

        {{-- Reportes (bloqueado por ahora) --}}
        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-100 opacity-60 relative overflow-hidden">
            <div class="bg-amber-50 text-amber-600 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                <i class="fa-solid fa-chart-line text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Estadísticas y Reportes</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Generación de informes detallados sobre consumo mensual y proyecciones de necesidades.
            </p>
            <span class="bg-amber-100 text-amber-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-200">Próximamente</span>
        </div>

    </div>
</div>
@endsection