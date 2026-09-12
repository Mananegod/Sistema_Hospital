<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class LoteHelper
{
    /**
     * Obtiene el ID de un lote existente por su código o crea uno nuevo en la tabla 'lotes'.
     *
     * @param string|null $codigoLote
     * @param string|null $fechaVencimiento (Opcional, formato Y-m-d)
     * @param string|null $proveedor (Opcional)
     * @return int|null
     */
    public static function obtenerOCrearLoteId(?string $codigoLote, ?string $fechaVencimiento = null, ?string $proveedor = null): ?int
    {
        $codigo = !empty($codigoLote) ? trim($codigoLote) : 'S/L';

        // Si el producto no especifica lote o es 'S/L', retornamos null o el ID de un lote genérico
        if ($codigo === 'S/L' || $codigo === '') {
            return null;
        }

        // 1. Buscamos si el lote ya existe en la tabla 'lotes'
        $lote = DB::table('lotes')
            ->where(DB::raw('LOWER(codigo_lote)'), '=', strtolower($codigo))
            ->first();

        if ($lote) {
            return $lote->id;
        }

        // 2. Si no existe, lo insertamos en la tabla 'lotes' y retornamos el ID generado
        return DB::table('lotes')->insertGetId([
            'codigo_lote'       => strtoupper($codigo),
            'fecha_vencimiento' => $fechaVencimiento ?? now()->addMonths(6)->toDateString(),
            'fecha_ingreso'     => now()->toDateString(),
            'proveedor'         => $proveedor,
            'estado'            => 'Disponible',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}