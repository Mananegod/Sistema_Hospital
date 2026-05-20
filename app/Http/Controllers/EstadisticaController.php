<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    public function index()
    {
        $pacientesHoy = DB::table('pacientes_internados')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $totalMedicamentos = DB::table('medicamentos')->count();

        $alertasStock = DB::table('medicamentos')
            ->whereRaw('cantidad_stock <= stock_minimo')
            ->count();

        $retirosHoy = DB::table('retiros')
            ->whereDate('created_at', now()->toDateString())
            ->sum('cantidad') ?? 0;

        $pacientesPorArea = DB::table('pacientes_internados')
            ->join('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select('areas.nombre_area as area', DB::raw('count(pacientes_internados.id) as total'))
            ->groupBy('areas.nombre_area')
            ->orderBy('total', 'desc')
            ->get();

        $topMedicamentos = DB::table('retiros')
            ->join('medicamentos', 'retiros.medicamento_id', '=', 'medicamentos.id')
            ->select('medicamentos.nombre_medicamento as nombre', DB::raw('sum(retiros.cantidad) as total_retirado'))
            ->groupBy('medicamentos.nombre_medicamento')
            ->orderBy('total_retirado', 'desc')
            ->limit(5)
            ->get();

        $retirosPorArea = DB::table('retiros')
            ->join('areas', 'retiros.area_id', '=', 'areas.id')
            ->select('areas.nombre_area as area', DB::raw('sum(retiros.cantidad) as total_insumos'))
            ->groupBy('areas.nombre_area')
            ->orderBy('total_insumos', 'desc')
            ->get();

        return view('estadisticas', compact(
            'pacientesHoy',
            'totalMedicamentos',
            'alertasStock',
            'retirosHoy',
            'pacientesPorArea',
            'topMedicamentos',
            'retirosPorArea'
        ));
    }
}