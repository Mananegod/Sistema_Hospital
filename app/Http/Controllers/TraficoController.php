<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class TraficoController extends Controller
{
    public function mantenerActivo(): JsonResponse
    {
        $estadoSimulacion = filter_var(config('app.simulador_trafico'), FILTER_VALIDATE_BOOLEAN);

        if (!$estadoSimulacion) {
            return response()->json(['estado' => 'inactivo'], 403);
        }

        try {
            DB::select('SELECT 1');
            
            return response()->json([
                'estado_servidor' => 'activo',
                'estado_base_datos' => 'activo'
            ], 200);
        } catch (Exception $excepcion) {
            return response()->json([
                'estado_servidor' => 'activo',
                'estado_base_datos' => 'inactivo',
                'error' => $excepcion->getMessage()
            ], 500);
        }
    }
}