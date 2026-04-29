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
            if (!isset($row['descripcion']) || empty($row['descripcion'])) continue;

            // 1. Asegurar que el medicamento existe en el catálogo
            DB::table('medicamentos')->updateOrInsert(
                ['nombre' => $row['descripcion']],
                [
                    'presentacion' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $medicamento = DB::table('medicamentos')->where('nombre', $row['descripcion'])->first();

            // 2. Actualizar o insertar el stock para el área específica
            DB::table('inventarios')->updateOrInsert(
                [
                    'medicamento_id' => $medicamento->id,
                    'area_id' => $this->areaId
                ],
                [
                    'stock_actual' => (int)($row['actual'] ?? 0),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function headingRow(): int
    {
        return 9; // El encabezado está en la fila 9
    }
}