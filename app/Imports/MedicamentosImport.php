<?php

namespace App\Imports;

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
            $nombreMedicamento = trim($row['descripcion'] ?? '');

            if (empty($nombreMedicamento)) {
                continue;
            }

            DB::table('medicamentos')->updateOrInsert(
                ['nombre_medicamento' => $nombreMedicamento],
                [
                    'nombre' => $nombreMedicamento, 
                    'cantidad_stock' => (int)($row['actual'] ?? 0),
                    'codigo_lote' => $row['lote'] ?? 'S/L', 
                    'fecha_vencimiento' => now()->addMonths(6), 
                    'status_disponibilidad' => ((int)($row['actual'] ?? 0) > 0) ? 'Disponible' : 'Agotado',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function headingRow(): int
    {
        return 9;
    }
}