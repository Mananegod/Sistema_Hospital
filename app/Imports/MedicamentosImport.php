<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class MedicamentosImport implements ToCollection, WithHeadingRow
{
    protected $areaId;

    public function __construct($areaId)
    {
        $this->areaId = $areaId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validamos que la fila tenga una descripción (nombre del medicamento)
            if (!isset($row['descripcion']) || empty($row['descripcion'])) {
                continue;
            }

            // Mapeo directo: lo que viene del Excel -> lo que va a la Base de Datos
            DB::table('medicamentos')->updateOrInsert(
                ['nombre_medicamento' => trim($row['descripcion'])],
                [
                    'nombre' => trim($row['descripcion']), 
                    'cantidad_stock' => (int)($row['actual'] ?? 0),
                    'area_destino' => $this->areaId,
                    'codigo_lote' => $row['lote'] ?? 'S/L', 
                    'fecha_vencimiento' => now()->addMonths(6), // Fecha tentativa para evitar errores
                    'status_disponibilidad' => ((int)($row['actual'] ?? 0) > 0) ? 'Disponible' : 'Agotado',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function headingRow(): int
    {
        // Tu archivo F15 tiene los encabezados en la fila 9
        return 9;
    }
}