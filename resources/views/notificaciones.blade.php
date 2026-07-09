@extends('layouts.app')

@section('title', 'Alertas de Inventario - Hospital TG')

@section('content')

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 9999px;
    }

    .pagination-btn {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .pagination-btn:hover {
        transform: translateY(-1px);
    }
    .page-number {
        min-width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        font-weight: 600;
        transition: all 0.2s;
        font-size: 0.875rem;
    }
    .page-number.active {
        background-color: #1e40af;
        color: white;
        border: none;
    }

    
    @media (max-width: 640px) {
        .page-number {
            min-width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
        .pagination-container {
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .pagination-buttons {
            flex-wrap: wrap;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .page-number {
            min-width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }
    }

    @media (max-width: 400px) {
        .page-number {
            min-width: 26px;
            height: 26px;
            font-size: 0.65rem;
        }
    }

    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 380px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6">

    {{-- Encabezado --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3 flex-wrap">
                Panel de Alertas Preventivas
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Monitoreo automático de stock e indicadores de vencimiento de medicamentos.</p>
        </div>
        <div class="text-xs font-semibold text-slate-400 bg-white border border-slate-100 px-4 py-2 rounded-sm shadow-sm whitespace-nowrap self-start">
            <i class="fa-regular fa-clock mr-1.5"></i> Último cálculo: Hoy, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </div>
    </div>

    <div class="space-y-8">

        {{-- Tarjetas Estadísticas --}}
        <div class="stats-grid grid grid-cols-1 min-[480px]:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5">
            <div class="bg-white p-4 sm:p-6 rounded-sm border border-slate-100 shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                <div class="bg-red-50 p-3 sm:p-4 rounded-sm text-red-600 border border-red-100/50 shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalCriticos }}</div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-tight">Stock Mínimo o Crítico</div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-sm border border-slate-100 shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                <div class="bg-rose-50 p-3 sm:p-4 rounded-sm text-rose-600 border border-rose-100/50 shrink-0">
                    <i class="fa-solid fa-skull-crossbones text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalVencidos }}</div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-tight">Lotes Expirados</div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-sm border border-slate-100 shadow-sm flex items-center gap-4 transition-transform hover:scale-[1.01]">
                <div class="bg-orange-50 p-3 sm:p-4 rounded-sm text-orange-600 border border-orange-100/50 shrink-0">
                    <i class="fa-solid fa-hourglass-half text-xl sm:text-2xl"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-2xl font-black text-slate-800 tracking-tight">{{ $totalPorVencer }}</div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-tight">Por Vencer (&lt; 30 días)</div>
                </div>
            </div>
        </div>

        {{-- TABLA 1: STOCK CRÍTICO --}}
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden" 
             x-data="stockPagination({{ $stockCritico->toJson() }})" x-init="init(); initResponsive()" x-on:resize.window="updateWidth">

            <div class="p-4 sm:p-6 border-b border-slate-50 flex items-center gap-3 bg-slate-50/40 flex-wrap">
                <div class="h-8 w-8 rounded-sm bg-red-100 text-red-600 flex items-center justify-center text-sm font-bold shadow-sm shrink-0">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Medicamentos con Stock Insuficiente</h2>
                    <p class="text-xs text-slate-400">Productos cuyas cantidades actuales están por debajo del umbral de reorden.</p>
                </div>
            </div>

            <div class="table-container custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100">
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Medicamento</th>
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Área de Destino</th>
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Stock Físico</th>
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Límite Establecido</th>
                            <th class="px-4 sm:px-8 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in paginatedItems" :key="index">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 sm:px-6 py-4 sm:py-6">
                                    <div class="font-bold text-slate-800 text-sm" x-text="item.medicamento"></div>
                                    <div class="text-[10px] font-semibold text-slate-400">ID Ref: #00<span x-text="item.id"></span></div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 sm:py-6 font-semibold text-xs text-slate-500" 
                                    x-text="item.area_destino || 'Almacén General'"></td>
                                <td class="px-4 sm:px-6 py-4 sm:py-6">
                                    <span class="text-sm font-black text-red-600 bg-red-50 px-2.5 py-1 rounded-sm border border-red-100" x-text="item.stock_actual"></span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 sm:py-6 font-bold text-xs text-slate-400">
                                    {{ $stockMinimoEstandar }} unidades
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 text-right">
                                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-sm text-[9px] font-black uppercase italic tracking-widest border border-red-200">
                                        Reordenar
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="paginatedItems.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-circle-check text-green-500 text-2xl mb-2"></i>
                                <p class="font-bold text-slate-700">¡Inventario Conforme!</p>
                                <p class="text-xs text-slate-400">No se detectaron medicamentos con stock crítico.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN CORREGIDA: números siempre visibles -->
            <div class="border-t border-slate-100 px-4 sm:px-6 py-5 bg-white">
                <div class="pagination-container flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-slate-500 text-center sm:text-left">
                        Mostrando <span class="font-semibold text-slate-700" x-text="startIndex"></span> - 
                        <span class="font-semibold text-slate-700" x-text="endIndex"></span> de 
                        <span class="font-semibold text-slate-700" x-text="items.length"></span>
                    </div>

                    <div class="flex items-center gap-1 pagination-buttons flex-wrap justify-center">
                        <!-- Anterior -->
                        <button @click="prevPage()" :disabled="currentPage === 1" 
                            class="pagination-btn w-9 h-9 flex items-center justify-center rounded-sm border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <!-- Números de página (siempre visibles, se adaptan con maxVisible) -->
                        <div class="flex items-center gap-1 pagination-numbers flex-wrap justify-center">
                            <template x-for="page in visiblePages" :key="page">
                                <button @click="goToPage(page)" 
                                    :disabled="page === '...'"
                                    :class="{
                                        'page-number active': currentPage === page,
                                        'page-number border border-slate-200 hover:bg-slate-50 text-slate-700': currentPage !== page && page !== '...',
                                        'w-9 h-9 flex items-center justify-center text-slate-400 cursor-default': page === '...'
                                    }"
                                    x-text="page === '...' ? '...' : page"></button>
                            </template>
                        </div>

                        <!-- Siguiente -->
                        <button @click="nextPage()" :disabled="currentPage === totalPages" 
                            class="pagination-btn w-9 h-9 flex items-center justify-center rounded-sm border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA 2: VENCIMIENTOS --}}
        <div class="bg-white rounded-sm border border-slate-100 shadow-sm overflow-hidden"
             x-data="vencimientoPagination({{ $alertasVencimiento->toJson() }})" x-init="init(); initResponsive()" x-on:resize.window="updateWidth">

            <div class="p-4 sm:p-6 border-b border-slate-50 flex items-center gap-3 bg-slate-50/40 flex-wrap">
                <div class="h-8 w-8 rounded-sm bg-orange-100 text-orange-600 flex items-center justify-center text-sm font-bold shadow-sm shrink-0">
                    <i class="fa-solid fa-calendar-circle-exclamation"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">Cronograma de Vencimientos y Caducidad</h2>
                    <p class="text-xs text-slate-400">Control de lotes críticos ordenados cronológicamente.</p>
                </div>
            </div>

            <div class="table-container custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100">
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Medicamento</th>
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Ubicación / Área</th>
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Existencia Afectada</th>
                            <th class="px-4 sm:px-6 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider">Fecha de Expiración</th>
                            <th class="px-4 sm:px-8 py-4 text-xs font-bold uppercase text-slate-400 tracking-wider text-right">Prioridad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(lote, index) in paginatedItems" :key="index">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 sm:px-6 py-4 sm:py-6">
                                    <div class="font-bold text-slate-800 text-sm" x-text="lote.medicamento"></div>
                                    <div class="text-[10px] font-bold text-slate-400">Medicamento en Lote</div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 sm:py-6 font-semibold text-xs text-slate-500" x-text="lote.area_destino || 'Almacén Central'"></td>
                                <td class="px-4 sm:px-6 py-4 sm:py-6">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm font-black text-slate-700" x-text="lote.unidades"></span>
                                        <span class="text-[10px] font-bold text-slate-400">Unidades</span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4 sm:py-6">
                                    <div class="font-mono text-xs font-bold" 
                                         :class="lote.estado_vencimiento === 'Vencido' ? 'text-rose-600' : 'text-orange-600'">
                                        <span x-text="lote.fecha_vencimiento_formatted"></span>
                                    </div>
                                    <div class="text-[9px] font-bold" 
                                         :class="lote.estado_vencimiento === 'Vencido' ? 'text-rose-400' : 'text-orange-400'">
                                        <span x-text="lote.dias_texto"></span>
                                    </div>
                                </td>
                                <td class="px-4 sm:px-8 py-4 sm:py-6 text-right">
                                    <span class="px-3 py-1 rounded-sm text-[9px] font-black uppercase italic tracking-widest border shadow-sm whitespace-nowrap"
                                          :class="lote.estado_vencimiento === 'Vencido' 
                                            ? 'bg-rose-100 text-rose-600 border-rose-200' 
                                            : 'bg-orange-100 text-orange-600 border-orange-200'">
                                        <span x-text="lote.estado_vencimiento === 'Vencido' ? 'Vencido (Bloqueado)' : 'Por Vencer'"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="paginatedItems.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-shield-heart text-blue-500 text-2xl mb-2"></i>
                                <p class="font-bold text-slate-700">¡Seguridad Garantizada!</p>
                                <p class="text-xs text-slate-400">No existen lotes próximos a vencer.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN CORREGIDA -->
            <div class="border-t border-slate-100 px-4 sm:px-6 py-5 bg-white">
                <div class="pagination-container flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-slate-500 text-center sm:text-left">
                        Mostrando <span class="font-semibold text-slate-700" x-text="startIndex"></span> - 
                        <span class="font-semibold text-slate-700" x-text="endIndex"></span> de 
                        <span class="font-semibold text-slate-700" x-text="items.length"></span>
                    </div>

                    <div class="flex items-center gap-1 pagination-buttons flex-wrap justify-center">
                        <button @click="prevPage()" :disabled="currentPage === 1" 
                            class="pagination-btn w-9 h-9 flex items-center justify-center rounded-sm border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <div class="flex items-center gap-1 pagination-numbers flex-wrap justify-center">
                            <template x-for="page in visiblePages" :key="page">
                                <button @click="goToPage(page)" 
                                    :disabled="page === '...'"
                                    :class="{
                                        'page-number active': currentPage === page,
                                        'page-number border border-slate-200 hover:bg-slate-50 text-slate-700': currentPage !== page && page !== '...',
                                        'w-9 h-9 flex items-center justify-center text-slate-400 cursor-default': page === '...'
                                    }"
                                    x-text="page === '...' ? '...' : page"></button>
                            </template>
                        </div>

                        <button @click="nextPage()" :disabled="currentPage === totalPages" 
                            class="pagination-btn w-9 h-9 flex items-center justify-center rounded-sm border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-50">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createPagination(initialData, storageKey) {
    return {
        items: initialData || [],
        currentPage: 1,
        itemsPerPage: 10,
        screenWidth: window.innerWidth,
        resizeListener: null,

        init() {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const page = parseInt(saved);
                if (page > 0 && page <= this.totalPages) this.currentPage = page;
            }
        },

        initResponsive() {
            this.updateWidth();
            this.resizeListener = () => this.updateWidth();
            window.addEventListener('resize', this.resizeListener);
        },

        updateWidth() {
            this.screenWidth = window.innerWidth;
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.items.length / this.itemsPerPage));
        },

        get paginatedItems() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.items.slice(start, start + this.itemsPerPage);
        },

        get startIndex() {
            return this.items.length ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
        },
        get endIndex() {
            return Math.min(this.currentPage * this.itemsPerPage, this.items.length);
        },

        get visiblePages() {
            let pages = [];
            const total = this.totalPages;
            const current = this.currentPage;
            // Determinar cuántos números mostrar según el ancho
            let maxVisible = 7;
            if (this.screenWidth < 640) maxVisible = 5;
            if (this.screenWidth < 480) maxVisible = 4;
            if (this.screenWidth < 400) maxVisible = 3;  // Para móviles muy pequeños

            if (total <= maxVisible) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                pages.push(1);
                if (current > 3) pages.push('...');
                let start = Math.max(2, current - 1);
                let end = Math.min(total - 1, current + 1);
                if (maxVisible <= 4) {
                    start = Math.max(2, current);
                    end = Math.min(total - 1, current);
                }
                for (let i = start; i <= end; i++) {
                    if (!pages.includes(i)) pages.push(i);
                }
                if (current < total - 2) pages.push('...');
                if (!pages.includes(total)) pages.push(total);
            }
            return pages.filter((v, i, a) => a.indexOf(v) === i);
        },

        goToPage(page) {
            if (page === '...' || page < 1 || page > this.totalPages) return;
            this.currentPage = page;
            localStorage.setItem(storageKey, page);
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                localStorage.setItem(storageKey, this.currentPage);
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                localStorage.setItem(storageKey, this.currentPage);
            }
        },

        destroy() {
            if (this.resizeListener) window.removeEventListener('resize', this.resizeListener);
        }
    }
}

function stockPagination(data) {
    return createPagination(data, 'stockPage');
}

function vencimientoPagination(data) {
    const base = createPagination((data || []).map(item => ({
        ...item,
        fecha_vencimiento_formatted: item.fecha_vencimiento ? 
            new Date(item.fecha_vencimiento).toLocaleDateString('es-ES') : '',
        dias_texto: item.estado_vencimiento === 'Vencido' 
            ? `Vencido hace ${Math.abs(item.dias_restantes)} días` 
            : `Vence en ${item.dias_restantes} días`
    })), 'vencimientoPage');
    return {
        ...base,
        initResponsive() {
            this.updateWidth();
            this.resizeListener = () => this.updateWidth();
            window.addEventListener('resize', this.resizeListener);
        },
        updateWidth() {
            this.screenWidth = window.innerWidth;
        }
    };
}
</script>
@endsection