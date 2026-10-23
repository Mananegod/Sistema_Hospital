@extends('layouts.app')

@section('title', 'Historial de Desechos y Mermas')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ modalDetalle: false, itemSeleccionado: {} }">
        
        {{-- Encabezado --}}
        <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Historial de Insumos caducados</h1>
                <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Registro oficial de mermas, lotes vencidos e incineraciones - Hospital Dr. Tiburcio Garrido</p>
            </div>
            <div>
                <a href="{{ route('almacen.index') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-sm transition shadow-sm text-xs uppercase tracking-wider">
                    <i class="fas fa-arrow-left"></i> Volver al Almacén
                </a>
            </div>
        </div>

        {{-- Tabla de Desechos / Bajas --}}
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-trash-alt text-red-600"></i> Lotes Desincorporados por Vencimiento o Deterioro
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[750px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/30">
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Tipo</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Nombre del Ítem</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Código de Lote</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">F. Vencimiento</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Cant. Desechada</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Motivo / Observación</th>
                            <th class="px-4 sm:px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Fecha de Baja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                        @forelse($desechos as $desecho)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-sm text-[11px] font-bold uppercase tracking-wide
                                        {{ $desecho->tipo_item === 'medicamento' ? 'bg-purple-50 text-purple-700 border border-purple-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                                        {{ $desecho->tipo_item }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-slate-900 font-bold tracking-tight">{{ $desecho->nombre_item }}</td>
                                <td class="px-4 sm:px-6 py-4 text-center font-mono text-xs font-bold text-slate-600">
                                    {{ $desecho->codigo_lote ?? 'S/L' }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-sm text-[11px] font-mono font-bold bg-red-100 text-red-700 border border-red-200">
                                        {{ \Carbon\Carbon::parse($desecho->fecha_vencimiento)->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center font-mono font-bold text-red-600 text-base">
                                    -{{ $desecho->cantidad }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-xs text-slate-600 italic">
                                    {{ $desecho->motivo }}
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-center text-xs font-mono text-slate-400 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($desecho->created_at)->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                    <i class="fas fa-clipboard-check text-2xl block mb-2 text-slate-300 not-italic"></i> No hay registros de desecho o mermas almacenados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($desechos, 'hasPages') && $desechos->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-slate-50 bg-slate-50/30 overflow-x-auto">
                    {{ $desechos->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection