@extends('layouts.app')

@section('title', 'Detalle de Lote')

@section('content')
    <div class="max-w-7xl mx-auto">
        
        {{-- Botón de regreso --}}
        <div class="mb-8">
            <a href="{{ route('almacen.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-900 transition uppercase tracking-wider mb-4">
                <i class="fas fa-arrow-left"></i> Volver al Almacén
            </a>
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-sm text-xs font-mono font-bold">
                        LOTE: {{ $codigo_lote }}
                    </span>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase mt-2">Insumos por Lote</h1>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/30">
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Insumo Médico</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide text-center">Stock Actual</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Área Ubicación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                        @foreach($medicamentos as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 text-slate-900 font-bold">
                                    {{ $item->nombre_medicamento ?? ($item->nombre ?? 'Insumo Sin Nombre') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono">
                                    {{ $item->cantidad_stock ?? 0 }}
                                </td>
                                <td class="px-6 py-4 uppercase text-xs">
                                    {{ $item->area_destino ?? 'No Asignada' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection