@extends('layouts.app')

@section('title', "Detalle del Lote {$lote->codigo_lote}")

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Trazabilidad del Lote</h1>
            <p class="text-xs font-mono font-bold text-blue-600 mt-1">CÓDIGO: {{ $lote->codigo_lote }}</p>
        </div>
        <a href="{{ route('lotes.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-sm uppercase tracking-wider transition">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Lista de Lotes
        </a>
    </div>

    {{-- Panel de Control Sanitario del Lote --}}
    <div class="bg-white p-6 rounded-sm border border-slate-100 shadow-sm mb-6">
        <h2 class="text-xs font-bold uppercase tracking-widest text-slate-700 mb-4 flex items-center gap-2">
            <i class="fas fa-notes-medical text-blue-600"></i> Estado Sanitario y Proveedor
        </h2>

        <form action="{{ route('lotes.update-estado', $lote->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Proveedor / Origen</label>
                    <input type="text" name="proveedor" value="{{ $lote->proveedor }}" placeholder="Ej. Ministerio de Salud / DROGUERIA X"
                           class="w-full bg-slate-50 border border-slate-200 rounded-sm px-4 py-2.5 text-xs font-bold text-slate-700 outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Estado Sanitario</label>
                    <select name="estado" class="w-full bg-slate-50 border border-slate-200 rounded-sm px-4 py-2.5 text-xs font-bold text-slate-700 outline-none">
                        <option value="Disponible" {{ $lote->estado === 'Disponible' ? 'selected' : '' }}>Disponible</option>
                        <option value="En Cuarentena" {{ $lote->estado === 'En Cuarentena' ? 'selected' : '' }}>En Cuarentena</option>
                        <option value="Defectuoso / Retirado" {{ $lote->estado === 'Defectuoso / Retirado' ? 'selected' : '' }}>Defectuoso / Retirado (Alerta)</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-4 rounded-sm text-xs uppercase tracking-wider transition shadow-sm">
                        <i class="fas fa-save mr-1"></i> Actualizar Lote
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wide">Observaciones / Motivo de Alerta Sanitaria</label>
                <textarea name="observaciones" rows="2" placeholder="Describa si hubo reportes de pacientes, frascos rotos o defectos detectados..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-sm p-3 text-xs font-semibold text-slate-700 outline-none">{{ $lote->observaciones }}</textarea>
            </div>
        </form>
    </div>

    {{-- MEDICAMENTOS VINCULADOS --}}
    <div class="bg-white rounded-sm border border-slate-100 shadow-sm mb-6 overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-pills text-blue-600"></i> Medicamentos asociados a este lote ({{ count($medicamentos) }})
            </h3>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-[10px] uppercase text-slate-400 font-bold bg-slate-50/30">
                    <th class="px-6 py-3">Nombre del Medicamento</th>
                    <th class="px-6 py-3">Tipo</th>
                    <th class="px-6 py-3 text-center">Stock Actual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
                @forelse($medicamentos as $med)
                    <tr>
                        <td class="px-6 py-3 font-bold text-slate-800">{{ $med->nombre_medicamento }}</td>
                        <td class="px-6 py-3 text-xs text-slate-500">{{ $med->tipo_insumo ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-center font-mono font-bold text-blue-600">{{ $med->cantidad_stock }} unds</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-xs text-slate-400 italic">No hay medicamentos en la tabla principal asociados a este lote.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- INSUMOS MÉDICOS VINCULADOS --}}
    <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-syringes text-emerald-600"></i> Insumos Médicos asociados a este lote ({{ count($insumos) }})
            </h3>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-[10px] uppercase text-slate-400 font-bold bg-slate-50/30">
                    <th class="px-6 py-3">Nombre del Insumo</th>
                    <th class="px-6 py-3">Tipo</th>
                    <th class="px-6 py-3 text-center">Stock Actual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm">
                @forelse($insumos as $ins)
                    <tr>
                        <td class="px-6 py-3 font-bold text-slate-800">{{ $ins->nombre_insumo }}</td>
                        <td class="px-6 py-3 text-xs text-slate-500">{{ $ins->tipo_insumo ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-center font-mono font-bold text-emerald-600">{{ $ins->cantidad_stock }} unds</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-6 text-center text-xs text-slate-400 italic">No hay insumos médicos asociados a este lote.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection