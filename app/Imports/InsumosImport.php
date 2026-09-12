<?php

namespace App\Imports;

use App\Helpers\LoteHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class InsumosImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Maatwebsite slugifica 'Descripcion' a 'descripcion'
            $nombreInsumo = trim(
                $row['descripcion'] 
                ?? $row['insumo'] 
                ?? $row['nombre'] 
                ?? $row['unnamed_1'] 
                ?? ''
            );

            // Evitar filas vacías o los nombres de encabezado
            if (empty($nombreInsumo) || strtolower($nombreInsumo) === 'descripcion' || strtolower($nombreInsumo) === 'items') {
                continue;
            }

            $cantidad = (int)($row['actual'] ?? $row['cantidad'] ?? $row['stock'] ?? 0);
            $lote = !empty($row['lote']) ? trim($row['lote']) : 'S/L';

            // Obtener o crear el registro en la tabla 'lotes' (FK)
            $loteId = LoteHelper::obtenerOCrearLoteId($lote);

            // Comprobamos si el insumo ya existe en insumos_medicos
            $existente = DB::table('insumos_medicos')
                ->where('nombre_insumo', $nombreInsumo)
                ->first();

            if ($existente) {
                DB::table('insumos_medicos')
                    ->where('id', $existente->id)
                    ->update([
                        'cantidad_stock' => $existente->cantidad_stock + $cantidad,
                        'codigo_lote'    => $lote,
                        'lote_id'        => $loteId, // Relación foránea activa
                        'updated_at'     => now(),
                    ]);
            } else {
                DB::table('insumos_medicos')->insert([
                    'nombre_insumo'  => $nombreInsumo,
                    'presentacion'   => null,
                    'cantidad_stock' => $cantidad,
                    'stock_minimo'   => 0,
                    'tipo_insumo'    => 'Por Determinar',
                    'codigo_lote'    => $lote,
                    'lote_id'        => $loteId, // Relación foránea activa
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    /**
     * La fila 2 del Excel contiene las verdaderas cabeceras: ['Items', 'Descripcion']
     */
    public function headingRow(): int
    {
        return 2;
    }
}