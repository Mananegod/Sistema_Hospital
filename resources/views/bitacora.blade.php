@extends('layouts.app')

@section('title', 'Bitácora de Auditoría')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-4">
    {{-- Encabezado responsivo --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-6 sm:mb-10">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight italic underline decoration-blue-500 decoration-4 underline-offset-8">
                Bitácora Global de Auditoría
            </h1>
            <p class="text-slate-500 mt-2 text-sm sm:text-base">Historial centralizado de movimientos en todos los módulos del sistema.</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl">
            <span class="text-blue-700 text-[10px] sm:text-xs font-bold uppercase tracking-wider">Control de Calidad Hospitalaria</span>
        </div>
    </div>

    {{-- Tabla responsiva --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left min-w-[500px]">
                <thead class="bg-slate-900 text-slate-400 text-[8px] sm:text-[10px] font-bold uppercase tracking-widest">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Módulo</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Acción</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4">Descripción del Evento</th>
                        <th class="px-3 sm:px-6 py-3 sm:py-4 text-right">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($registros as $reg)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-3 sm:px-6 py-4 sm:py-5">
                            <span class="px-2 py-1 rounded-lg text-[8px] sm:text-[10px] font-black uppercase shadow-sm border 
                                {{ $reg->modulo == 'Personal' ? 'bg-purple-50 text-purple-700 border-purple-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                {{ $reg->modulo }}
                            </span>
                        </td>
                        <td class="px-3 sm:px-6 py-4 sm:py-5 font-bold text-xs sm:text-sm">
                            @if(str_contains(strtolower($reg->accion), 'elimin'))
                                <span class="text-red-600 flex items-center gap-1">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $reg->accion }}
                                </span>
                            @else
                                <span class="text-emerald-600 flex items-center gap-1">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $reg->accion }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 sm:px-6 py-4 sm:py-5 text-slate-600 text-xs sm:text-sm font-medium leading-relaxed">
                            {{ $reg->descripcion }}
                            <p class="text-[8px] sm:text-[10px] text-slate-400 mt-1 uppercase tracking-tighter">Realizado por: {{ $reg->usuario }}</p>
                        </td>
                        <td class="px-3 sm:px-6 py-4 sm:py-5 text-right whitespace-nowrap">
                            <p class="text-xs sm:text-sm font-bold text-slate-700">{{ $reg->created_at->format('d/m/Y') }}</p>
                            <p class="text-[8px] sm:text-[10px] text-slate-400 font-mono italic">{{ $reg->created_at->format('h:i A') }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-3 sm:px-6 py-12 sm:py-20 text-center">
                            <div class="flex flex-col items-center opacity-20">
                                <svg class="w-12 h-12 sm:w-16 sm:h-16 mb-2 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="font-bold text-base sm:text-xl uppercase tracking-widest">Sin registros de actividad</p>
                                <p class="text-xs sm:text-sm">La bitácora está limpia por ahora.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tarjeta de resguardo --}}
    <div class="mt-6 sm:mt-8 p-4 sm:p-6 bg-slate-900 rounded-2xl text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="p-2 sm:p-3 bg-blue-600 rounded-xl">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs sm:text-sm font-bold">Respaldo Inmutable Hospital TG</p>
                <p class="text-[8px] sm:text-[10px] text-slate-400 uppercase tracking-widest">Los registros no pueden ser eliminados permanentemente por políticas de seguridad.</p>
            </div>
        </div>
    </div>
</div>
    @endsection