@extends('layouts.app')

@section('title', 'Gestión y Trazabilidad de Lotes')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Encabezado --}}
    <div class="mb-6 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Control de Lotes / Trazabilidad</h1>
            <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Administración de Lotes, Fechas de Vencimiento y Alertas Sanitarias</p>
        </div>
        <a href="{{ route('almacen.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-sm uppercase tracking-wider transition">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Inventario
        </a>
    </div>

    {{-- Tarjetas de Estado General --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 bg-white border border-slate-100 rounded-sm shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Lotes Registrados</p>
                <p class="text-2xl font-black font-mono text-slate-800 mt-1">{{ number_format($totalLotes) }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-sm flex items-center justify-center font-bold">
                <i class="fas fa-boxes text-lg"></i>
            </div>
        </div>

        <div class="p-4 bg-amber-50/60 border border-amber-100 rounded-sm shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-amber-700 uppercase tracking-widest">En Cuarentena</p>
                <p class="text-2xl font-black font-mono text-amber-800 mt-1">{{ number_format($lotesCuarentena) }}</p>
            </div>
            <div class="w-10 h-10 bg-amber-500 text-white rounded-sm flex items-center justify-center font-bold">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
        </div>

        <div class="p-4 bg-red-50/60 border border-red-100 rounded-sm shadow-xs flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-red-700 uppercase tracking-widest">Defectuosos / Retirados</p>
                <p class="text-2xl font-black font-mono text-red-800 mt-1">{{ number_format($lotesDefectuosos) }}</p>
            </div>
            <div class="w-10 h-10 bg-red-600 text-white rounded-sm flex items-center justify-center font-bold">
                <i class="fas fa-ban text-lg"></i>
            </div>
        </div>
    </div>

    {{-- Filtros y Búsqueda --}}
    <div class="bg-white p-4 rounded-sm border border-slate-100 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('lotes.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por Lote o Proveedor..."
                   class="bg-slate-50 border border-slate-200 rounded-sm px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 w-64">

            <select name="estado" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-sm px-4 py-2 text-xs font-bold text-slate-700 outline-none">
                <option value="">Todos los Estados</option>
                <option value="Disponible" {{ $estado === 'Disponible' ? 'selected' : '' }}>Disponible</option>
                <option value="En Cuarentena" {{ $estado === 'En Cuarentena' ? 'selected' : '' }}>En Cuarentena</option>
                <option value="Defectuoso / Retirado" {{ $estado === 'Defectuoso / Retirado' ? 'selected' : '' }}>Defectuoso / Retirado</option>
            </select>

            <button type="submit" class="bg-blue-600 text-white font-bold px-4 py-2 rounded-sm hover:bg-blue-700 transition text-xs uppercase tracking-wider">
                <i class="fas fa-search mr-1"></i> Filtrar
            </button>

            @if($search || $estado)
                <a href="{{ route('lotes.index') }}" class="text-xs font-bold text-red-500 hover:underline uppercase tracking-wider">
                    Limpiar Filtros
                </a>
            @endif
        </form>
    </div>

    {{-- Tabla de Lotes --}}
    <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/30 text-[10px] font-bold text-slate-400 uppercase tracking-wide">
                        <th class="px-6 py-4">Código de Lote</th>
                        <th class="px-6 py-4">Proveedor</th>
                        <th class="px-6 py-4 text-center">Ingreso</th>
                        <th class="px-6 py-4 text-center">Vencimiento</th>
                        <th class="px-6 py-4 text-center">Estado Sanitario</th>
                        <th class="px-6 py-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm font-medium text-slate-600">
                    @forelse($lotes as $lote)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-blue-600">
                                <a href="{{ route('lotes.show', $lote->id) }}" class="hover:underline flex items-center gap-1.5">
                                    <i class="fas fa-barcode text-slate-400"></i> {{ $lote->codigo_lote }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-700">
                                {{ $lote->proveedor ?? 'No especificado' }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">
                                {{ $lote->fecha_ingreso ? \Carbon\Carbon::parse($lote->fecha_ingreso)->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-xs font-bold {{ ($lote->fecha_vencimiento && \Carbon\Carbon::parse($lote->fecha_vencimiento)->isPast()) ? 'text-red-600' : 'text-slate-700' }}">
                                {{ $lote->fecha_vencimiento ? \Carbon\Carbon::parse($lote->fecha_vencimiento)->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-sm text-[10px] font-extrabold uppercase tracking-wider
                                    @if($lote->estado === 'Disponible') bg-emerald-50 text-emerald-700 border border-emerald-200
                                    @elseif($lote->estado === 'En Cuarentena') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-red-50 text-red-700 border border-red-200 @endif">
                                    {{ $lote->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('lotes.show', $lote->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-sm uppercase tracking-wider transition">
                                    <i class="fas fa-eye mr-1"></i> Trazabilidad
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                <i class="fas fa-box-open text-2xl block mb-2 text-slate-300 not-italic"></i> No se encontraron lotes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lotes->hasPages())
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/30">
                {{ $lotes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection