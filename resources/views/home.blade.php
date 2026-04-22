
@extends('layouts.app')

@section('title', 'Inicio - Hospital TG')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header hero --}}
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-8 md:p-12 mb-12 text-center shadow-xl">
        <span class="inline-block px-4 py-1.5 bg-blue-500/20 border border-blue-500/30 rounded-full text-blue-300 text-xs font-bold uppercase tracking-wider mb-4">
            Panel de Administración
        </span>
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
            Sistema Integral de Gestión <br>
            <span class="text-blue-500">Hospital Dr. Tiburcio Garrido</span>
        </h1>
        <p class="text-slate-400 max-w-2xl mx-auto text-base md:text-lg">
            Bienvenido al panel central. Seleccione el módulo correspondiente para gestionar los recursos, personal y estadísticas de la institución.
        </p>
    </div>

    {{-- Tarjetas de módulos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {{-- Inventario --}}
        <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all hover:-translate-y-1">
            <div class="bg-blue-50 text-blue-600 w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Gestión de Inventario</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Control total de medicamentos, insumos médicos y stock por áreas (Urgencias, UCI, Pediatría).
            </p>
            <a href="{{ route('medicamentos.index') }}" class="inline-flex items-center text-blue-600 font-semibold gap-2 group">
                Entrar al módulo
                <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>

        {{-- Pacientes (bloqueado) --}}
        <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-100 opacity-60 relative overflow-hidden">
            <div class="bg-emerald-50 text-emerald-600 w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Registro de Pacientes</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Historiales clínicos digitales, ingresos y gestión de camas por departamento médico.
            </p>
            <span class="bg-emerald-100 text-emerald-600 px-3 py-1 rounded-lg text-xs font-bold uppercase">Próximamente</span>
        </div>

        {{-- Reportes (bloqueado) --}}
        <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-100 opacity-60 relative overflow-hidden">
            <div class="bg-amber-50 text-amber-600 w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Reportes Estadísticos</h3>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Generación de informes mensuales de consumo y necesidades críticas del hospital.
            </p>
            <span class="bg-amber-100 text-amber-600 px-3 py-1 rounded-lg text-xs font-bold uppercase">Próximamente</span>
        </div>
    </div>
</div>
@endsection