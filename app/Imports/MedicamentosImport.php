<?php

namespace App\Imports;

use App\Helpers\LoteHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class MedicamentosImport implements ToCollection, WithHeadingRow
{
    public function __construct()
    {
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Maatwebsite slugifica 'Descripcion' a 'descripcion'
            $nombreMedicamento = trim(
                $row['descripcion'] 
                ?? $row['nombre'] 
                ?? $row['medicamento'] 
                ?? $row['unnamed_1'] 
                ?? ''
            );

            // Evitar filas vacías o los nombres de encabezado
            if (empty($nombreMedicamento) || strtolower($nombreMedicamento) === 'descripcion' || strtolower($nombreMedicamento) === 'items') {
                continue;
            }

            $cantidad = (int)($row['actual'] ?? $row['cantidad'] ?? $row['stock'] ?? 0);
            $lote = !empty($row['lote']) ? trim($row['lote']) : 'S/L';

            // Obtener o crear el registro en la tabla 'lotes' (FK)
            $loteId = LoteHelper::obtenerOCrearLoteId($lote);

            // Comprobamos si el medicamento ya existe en la tabla
            $existente = DB::table('medicamentos')
                ->where('nombre_medicamento', $nombreMedicamento)
                ->first();

            if ($existente) {
                $nuevoStock = $existente->cantidad_stock + $cantidad;
                DB::table('medicamentos')
                    ->where('id', $existente->id)
                    ->update([
                        'nombre'                => $nombreMedicamento,
                        'cantidad_stock'        => $nuevoStock,
                        'codigo_lote'           => $lote,
                        'lote_id'               => $loteId, // Relación foránea activa
                        'status_disponibilidad' => ($nuevoStock > 0) ? 'Disponible' : 'Agotado',
                        'updated_at'            => now(),
                    ]);
            } else {
                DB::table('medicamentos')->insert([
                    'nombre_medicamento'    => $nombreMedicamento,
                    'nombre'                => $nombreMedicamento,
                    'cantidad_stock'        => $cantidad,
                    'stock_minimo'          => 0,
                    'tipo_insumo'           => 'Por Determinar',
                    'codigo_lote'           => $lote,
                    'lote_id'               => $loteId, // Relación foránea activa
                    'fecha_vencimiento'     => now()->addMonths(6),
                    'status_disponibilidad' => ($cantidad > 0) ? 'Disponible' : 'Agotado',
                    'created_at'            => now(),
                    'updated_at'            => now(),
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