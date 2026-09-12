@extends('layouts.app')

@section('title', "Detalles del Lote {$codigo_lote}")

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Trazabilidad de Lote</h1>
            <p class="text-xs font-mono font-bold text-blue-600 mt-1">LOTE: {{ $codigo_lote }}</p>
        </div>
        <a href="{{ route('almacen.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-sm uppercase tracking-wider transition">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Almacén
        </a>
    </div>

    {{-- MEDICAMENTOS ENCONTRADOS --}}
    @if(isset($medicamentos) && $medicamentos->isNotEmpty())
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm mb-6 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-pills text-blue-600"></i>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Medicamentos asociados</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] uppercase text-slate-400 font-bold bg-slate-50/50">
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3 text-center">Stock Actual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @foreach($medicamentos as $med)
                        <tr>
                            <td class="px-6 py-3 font-bold text-slate-800">{{ $med->nombre_medicamento }}</td>
                            <td class="px-6 py-3 text-xs text-slate-500">{{ $med->tipo_insumo ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-center font-mono font-bold text-blue-600">{{ $med->cantidad_stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- INSUMOS MÉDICOS ENCONTRADOS --}}
    @if(isset($insumos) && $insumos->isNotEmpty())
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center gap-2">
                <i class="fas fa-syringes text-emerald-600"></i>
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest">Insumos Médicos asociados</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] uppercase text-slate-400 font-bold bg-slate-50/50">
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3 text-center">Stock Actual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @foreach($insumos as $ins)
                        <tr>
                            <td class="px-6 py-3 font-bold text-slate-800">{{ $ins->nombre_insumo }}</td>
                            <td class="px-6 py-3 text-xs text-slate-500">{{ $ins->tipo_insumo ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-center font-mono font-bold text-emerald-600">{{ $ins->cantidad_stock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection