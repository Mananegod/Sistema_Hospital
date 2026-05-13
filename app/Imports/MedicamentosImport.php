<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class MedicamentosImport implements SkipsEmptyRows, ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow, WithReadFilter, WithValidation
{
    protected $areaId;
    protected $nombreArea;

    public function __construct($areaId)
    {
        $this->areaId = $areaId;
        $area = DB::table('areas')->where('id', $areaId)->first();
        $this->nombreArea = $area ? $area->nombre_area : 'ALMACEN';
    }

    public function collection(Collection $rows)
    {
        $upsertData = [];
        $now = now()->toDateTimeString();
        $fechaVencimiento = now()->addMonths(6)->toDateString();

        foreach ($rows as $row) {
            $nombreMedicamento = trim($row['descripcion'] ?? '');

            if (empty($nombreMedicamento)) {
                continue;
            }

            $cantidad = (int) ($row['actual'] ?? 0);

            // Clave compuesta: nombre_medicamento + area_destino
            $key = $nombreMedicamento . '||' . $this->nombreArea;
            
            $upsertData[$key] = [
                'nombre_medicamento'    => $nombreMedicamento,
                'nombre'                => $nombreMedicamento,
                'cantidad_stock'        => $cantidad,
                'area_destino'          => $this->nombreArea,
                'codigo_lote'           => $row['lote'] ?? 'S/L',
                'fecha_vencimiento'     => $fechaVencimiento,
                'status_disponibilidad' => $cantidad > 0 ? 'Disponible' : 'Agotado',
                'created_at'            => $now,
                'updated_at'            => $now,
            ];
        }

        if (! empty($upsertData)) {
            // Upsert masivo usando la clave única compuesta
            DB::table('medicamentos')->upsert(
                array_values($upsertData),
                ['nombre_medicamento', 'area_destino'], // ← AMBAS columnas
                [
                    'nombre',
                    'cantidad_stock',
                    'codigo_lote',
                    'fecha_vencimiento',
                    'status_disponibilidad',
                    'updated_at',
                ]
            );
        }
    }

    public function headingRow(): int
    {
        return 9; // ← Ajusta a 1 si los encabezados están en la primera fila
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function readFilter(): IReadFilter
    {
        return new class implements IReadFilter {
            private $columns = ['A', 'B', 'C'];

            public function readCell($columnAddress, $row, $worksheetName = '')
            {
                $column = preg_replace('/\d/', '', $columnAddress);
                return in_array($column, $this->columns);
            }
        };
    }

    public function rules(): array
    {
        return [
            'descripcion' => 'nullable|string',
            'actual'      => 'nullable|integer|min:0',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'actual.integer' => 'El campo "actual" debe ser un número entero.',
            'actual.min'     => 'El campo "actual" no puede ser negativo.',
        ];
    }
}