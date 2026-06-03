@extends('layouts.app')

@section('title', 'Control de Estadísticas Hospitalarias')

@section('content')
@include('components.loading-overlay')

{{-- Inclusión de la librería Chart.js desde CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto" x-data="{ init() { if(this.$store.loading) this.$store.loading.close(); } }">
    
    {{-- Encabezado del Módulo --}}
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight uppercase">Control de Estadísticas</h1>
        <p class="text-slate-500 mt-1 uppercase text-xs tracking-wider">Panel analítico del Hospital "Dr. Tiburcio Garrido" - Inventario y Admisión Diaria.</p>
    </div>

    {{-- Bloque de Tarjetas / Indicadores Rápidos --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        
        {{-- Tarjeta 1: Pacientes de Hoy --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-user-injured"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Pacientes Hoy</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $pacientesHoy }}</h3>
            </div>
        </div>

        {{-- Tarjeta 2: Total Medicamentos --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-lg">
                <i class="fas fa-pills"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Ítems Catálogo</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $totalMedicamentos }}</h3>
            </div>
        </div>

        {{-- Tarjeta 3: Alertas de Stock Crítico --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl {{ $alertasStock > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-lg">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Stock Crítico</span>
                <h3 class="text-2xl font-black {{ $alertasStock > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">{{ $alertasStock }}</h3>
            </div>
        </div>

        {{-- Tarjeta 4: Unidades Retiradas Hoy --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-dolly-flatbed"></i>
            </div>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Despachos Hoy</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $retirosHoy }} <span class="text-xs font-normal text-slate-400">Uds.</span></h3>
            </div>
        </div>

    </div>

    {{-- Fila de Gráficos 1 --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
        
        {{-- Gráfico: Pacientes por Servicio (Ocupación) --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs md:col-span-2">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-50 pb-4">
                <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">
                    <i class="fas fa-hospital-user"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Pacientes Internados por Servicio</h3>
            </div>
            <div class="relative w-full h-64">
                <canvas id="chartPacientes"></canvas>
            </div>
        </div>

        {{-- Gráfico Circular: Consumo Total por Áreas Despachadas --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-50 pb-4">
                <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Distribución de Consumo</h3>
            </div>
            <div class="relative w-full h-64 flex items-center justify-center">
                <canvas id="chartAreas"></canvas>
            </div>
        </div>

    </div>

    {{-- Fila de Gráficos 2 --}}
    <div class="grid grid-cols-1 gap-8">
        
        {{-- Gráfico de Líneas u Horizontal: Top Medicamentos Más Demandados --}}
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-50 pb-4">
                <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-slate-700">Top 5 Medicamentos de Mayor Rotación (Unidades Retiradas)</h3>
            </div>
            <div class="relative w-full h-72">
                <canvas id="chartTopMedicamentos"></canvas>
            </div>
        </div>

    </div>

</div>

{{-- Bloque de Scripts para Renderizado de los Gráficos --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // 1. Corrección del contexto a '2d' para habilitar el motor de renderizado gráfico de Chart.js
        const ctxPacientes = document.getElementById('chartPacientes').getContext('2d');
        const datosPacientes = @json($pacientesPorArea);
        
        new Chart(ctxPacientes, {
            type: 'bar',
            data: {
                labels: datosPacientes.map(item => item.area),
                datasets: [{
                    label: 'Pacientes Activos',
                    data: datosPacientes.map(item => item.total),
                    backgroundColor: '#2563EB', 
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { color: '#94A3B8', font: { weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { color: '#64748B', font: { size: 10, weight: 'bold' } } }
                }
            }
        });

        // 2. Corrección del contexto a '2d' para el gráfico circular tipo Doughnut
        const ctxAreas = document.getElementById('chartAreas').getContext('2d');
        const datosRetirosArea = @json($retirosPorArea);

        new Chart(ctxAreas, {
            type: 'doughnut',
            data: {
                labels: datosRetirosArea.slice(0, 5).map(item => item.area), // Tomar las principales 5
                datasets: [{
                    data: datosRetirosArea.slice(0, 5).map(item => item.total_insumos),
                    backgroundColor: ['#0F172A', '#2563EB', '#F59E0B', '#10B981', '#6366F1'], // Paleta Slate, Blue, Amber, Emerald, Indigo
                    borderWidth: 4,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, color: '#475569', font: { size: 11, weight: 'bold' } } }
                }
            }
        });

        // 3. Corrección del contexto a '2d' y remoción de tokens residuales de sintaxis inválida en los ticks del eje Y
        const ctxTop = document.getElementById('chartTopMedicamentos').getContext('2d');
        const datosTop = @json($topMedicamentos);

        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: datosTop.map(item => item.nombre),
                datasets: [{
                    label: 'Unidades Despachadas',
                    data: datosTop.map(item => item.total_retirado),
                    backgroundColor: '#F59E0B',
                    borderRadius: 10,
                    maxBarThickness: 25
                }]
            },
            options: {
                indexAxis: 'y', 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { color: '#94A3B8' } },
                    y: { grid: { display: false }, ticks: { color: '#0F172A', font: { weight: 'bold', size: 11 } } }
                }
            }
        });

    });
</script>
@endsection