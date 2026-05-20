<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class MedicamentosImport implements ToCollection, WithHeadingRow
{
    protected $areaId;
    protected $nombreArea;

    public function __construct($areaId)
    {
        $this->areaId = $areaId;
        
        // CORRECCIÓN: Buscamos el nombre real del área una sola vez al iniciar la importación
        $area = DB::table('areas')->where('id', $areaId)->first();
        $this->nombreArea = $area ? $area->nombre_area : 'ALMACEN';
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validamos que la fila tenga una descripción (nombre del medicamento)
            // Usamos array_key_exists o isset con el nombre exacto que tenga el encabezado del Excel
            $nombreMedicamento = trim($row['descripcion'] ?? '');

            if (empty($nombreMedicamento)) {
                continue;
            }

            // Mapeo directo: lo que viene del Excel -> lo que va a la Base de Datos
            DB::table('medicamentos')->updateOrInsert(
                ['nombre_medicamento' => $nombreMedicamento],
                [
                    'nombre' => $nombreMedicamento, 
                    'cantidad_stock' => (int)($row['actual'] ?? 0),
                    
                    // CORRECCIÓN: Aquí usamos el nombre del área que buscamos en el constructor
                    'area_destino' => $this->nombreArea, 
                    
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
        // Tu archivo F15 tiene los encabezados en la fila 9
        return 9;
    }
}