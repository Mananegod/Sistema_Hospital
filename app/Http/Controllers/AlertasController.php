<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlertasController extends Controller
{
    public function index()
    {
        $hoy = Carbon::now()->format('Y-m-d');
        $dentroDe30Dias = Carbon::now()->addDays(30)->format('Y-m-d');
        
        // Umbral de stock mínimo estándar para las alertas de reorden (10 unidades)
        $stockMinimoEstandar = 10;

        // 1. Obtener Medicamentos en Stock Crítico (Se eliminó 'area_destino')
        $stockCritico = DB::table('medicamentos')
            ->select(
                'id',
                'nombre_medicamento as medicamento',
                'cantidad_stock as stock_actual'
            )
            ->where('cantidad_stock', '<=', $stockMinimoEstandar)
            ->orderBy('cantidad_stock', 'asc')
            ->get();

        // 2. Obtener Alertas de Vencimiento (Se eliminó 'area_destino')
        $alertasVencimiento = DB::table('medicamentos')
            ->select(
                'id',
                'nombre_medicamento as medicamento',
                'cantidad_stock as unidades',
                'fecha_vencimiento',
                // Evaluamos dinámicamente el estado del lote en base a la fecha actual
                DB::raw("CASE 
                    WHEN fecha_vencimiento < '$hoy' THEN 'Vencido'
                    WHEN fecha_vencimiento BETWEEN '$hoy' AND '$dentroDe30Dias' THEN 'Por Vencer'
                    ELSE 'Seguro'
                END as estado_vencimiento"),
                // PostgreSQL: Resta de fechas devuelve directamente la diferencia exacta en número de días
                DB::raw("(fecha_vencimiento - CURRENT_DATE) as dias_restantes")
            )
            ->where('fecha_vencimiento', '<=', $dentroDe30Dias)
            ->where('cantidad_stock', '>', 0) // Alertar solo si todavía quedan existencias
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        // 3. Contadores para las tarjetas informativas superiores
        $totalCriticos = $stockCritico->count();
        $totalVencidos = $alertasVencimiento->where('estado_vencimiento', 'Vencido')->count();
        $totalPorVencer = $alertasVencimiento->where('estado_vencimiento', 'Por Vencer')->count();

        // Retornamos la vista pasando todas las variables necesarias
        return view('notificaciones', compact(
            'stockCritico',
            'alertasVencimiento',
            'totalCriticos',
            'totalVencidos',
            'totalPorVencer',
            'stockMinimoEstandar'
        ));
    }
}