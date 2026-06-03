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
        
        // Definimos un umbral de stock mínimo estándar para las alertas de reorden (ej: 10 unidades)
        $stockMinimoEstandar = 10;

        // 1. Obtener Medicamentos en Stock Crítico usando tus columnas reales
        $stockCritico = DB::table('medicamentos')
            ->select(
                'id',
                'nombre_medicamento as medicamento',
                'cantidad_stock as stock_actual',
                'area_destino'
            )
            ->where('cantidad_stock', '<=', $stockMinimoEstandar)
            ->orderBy('cantidad_stock', 'asc')
            ->get();

        // 2. Obtener Alertas de Vencimiento usando tu columna 'fecha_vencimiento' real
        $alertasVencimiento = DB::table('medicamentos')
            ->select(
                'id',
                'nombre_medicamento as medicamento',
                'area_destino',
                'cantidad_stock as unidades',
                'fecha_vencimiento',
                // Evaluamos dinámicamente el estado del lote en base a la fecha actual
                DB::raw("CASE 
                    WHEN fecha_vencimiento < '$hoy' THEN 'Vencido'
                    WHEN fecha_vencimiento BETWEEN '$hoy' AND '$dentroDe30Dias' THEN 'Por Vencer'
                    ELSE 'Seguro'
                END as estado_vencimiento"),
                // PostgreSQL: Calcula la diferencia exacta de días entre la fecha de vencimiento y el día de hoy
                DB::raw("EXTRACT(DAY FROM AGE(fecha_vencimiento, '$hoy')) as dias_restantes")
                
                // NOTA: Si llegas a migrar tu base de datos local a MySQL, descomenta la línea de abajo y borra la de arriba:
                // DB::raw("DATEDIFF(fecha_vencimiento, '$hoy') as dias_restantes")
            )
            ->where('fecha_vencimiento', '<=', $dentroDe30Dias)
            ->where('cantidad_stock', '>', 0) // Alertar solo si todavía quedan existencias físicas
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();

        // 3. Contadores para las tarjetas informativas superiores
        $totalCriticos = $stockCritico->count();
        $totalVencidos = $alertasVencimiento->where('estado_vencimiento', 'Vencido')->count();
        $totalPorVencer = $alertasVencimiento->where('estado_vencimiento', 'Por Vencer')->count();

        // Retornamos la vista inyectando el valor estandard de stock mínimo junto a las colecciones
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