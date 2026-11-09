<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenFdaVademecumSeeder extends Seeder
{
    /**
     * Lista base de principios activos / medicamentos clave a poblar.
     */
    protected array $medicamentosBase = [
        [
            'nombre_generico' => 'Paracetamol',
            'query_openfda' => 'acetaminophen',
            'nombre_comercial' => 'Atamel',
            'forma_farmaceutica' => 'Tableta',
            'concentracion' => '500 mg',
            'descripcion_presentacion' => 'Caja de 10 tabletas',
            'cantidad' => 10,
            'unidad' => 'tabletas',
        ],
        [
            'nombre_generico' => 'Ibuprofeno',
            'query_openfda' => 'ibuprofen',
            'nombre_comercial' => 'Ibuprofeno 400mg',
            'forma_farmaceutica' => 'Capsula Blanda',
            'concentracion' => '400 mg',
            'descripcion_presentacion' => 'Caja de 20 cápsulas',
            'cantidad' => 20,
            'unidad' => 'capsulas',
        ],
        [
            'nombre_generico' => 'Amoxicilina',
            'query_openfda' => 'amoxicillin',
            'nombre_comercial' => 'Amoxal',
            'forma_farmaceutica' => 'Jarabe / Suspensión',
            'concentracion' => '250 mg / 5ml',
            'descripcion_presentacion' => 'Frasco 100ml',
            'cantidad' => 100,
            'unidad' => 'ml',
        ],
        [
            'nombre_generico' => 'Omeprazol',
            'query_openfda' => 'omeprazole',
            'nombre_comercial' => 'Ulceran',
            'forma_farmaceutica' => 'Capsula',
            'concentracion' => '20 mg',
            'descripcion_presentacion' => 'Caja de 14 cápsulas',
            'cantidad' => 14,
            'unidad' => 'capsulas',
        ],
        [
            'nombre_generico' => 'Loratadina',
            'query_openfda' => 'loratadine',
            'nombre_comercial' => 'Clarityne',
            'forma_farmaceutica' => 'Tableta',
            'concentracion' => '10 mg',
            'descripcion_presentacion' => 'Caja de 10 tabletas',
            'cantidad' => 10,
            'unidad' => 'tabletas',
        ],
    ];

    public function run(): void
    {
        // 1. Asegurar la fuente externa "openFDA" y obtener el registro real de la BD
        DB::table('fuentes_vademecum')->updateOrInsert(
            ['nombre' => 'openFDA'],
            [
                'tipo' => 'api',
                'activo' => DB::raw('true'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $fuente = DB::table('fuentes_vademecum')->where('nombre', 'openFDA')->first();

        // 2. Crear registro de sincronización iniciada utilizando el ID seguro de la fuente
        $syncId = DB::table('sincronizaciones_vademecum')->insertGetId([
            'fuente_id' => $fuente->id,
            'tipo_operacion' => 'consulta_api',
            'estado' => 'iniciada',
            'registros_procesados' => 0,
            'fecha_inicio' => now(),
        ]);

        $procesados = 0;

        foreach ($this->medicamentosBase as $medData) {
            // A. Registrar o buscar Forma Farmacéutica
            $formaId = null;
            if (!empty($medData['forma_farmaceutica'])) {
                $forma = DB::table('formas_farmaceuticas')->where('nombre', $medData['forma_farmaceutica'])->first();
                if (!$forma) {
                    $formaId = DB::table('formas_farmaceuticas')->insertGetId([
                        'nombre' => $medData['forma_farmaceutica'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $formaId = $forma->id;
                }
            }

            // B. Consultar la API pública de openFDA
            $fdaData = $this->consultarOpenFDA($medData['query_openfda']);

            // C. Insertar en catalogo_medicamentos
            $catalogoId = DB::table('catalogo_medicamentos')->insertGetId([
                'nombre_generico' => $medData['nombre_generico'],
                'nombre_comercial' => $medData['nombre_comercial'],
                'concentracion' => $medData['concentracion'],
                'forma_farmaceutica_id' => $formaId,
                'condicion_dispensa' => 'Bajo receta médica',
                'almacenamiento' => 'Conservar a temperatura ambiente inferior a 30°C',
                'indicaciones' => $fdaData['indicaciones'] ?? 'Sin información devuelta por la API',
                'dosificacion' => $fdaData['dosificacion'] ?? 'Consultar prescriptor técnico',
                'contraindicaciones' => $fdaData['contraindicaciones'] ?? 'Sin contraindicaciones registradas en etiqueta',
                'advertencias' => $fdaData['advertencias'] ?? 'Uso bajo supervisión profesional',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // D. Insertar Presentación Comercial
            DB::table('presentaciones_catalogo')->insert([
                'catalogo_medicamento_id' => $catalogoId,
                'descripcion' => $medData['descripcion_presentacion'],
                'cantidad' => $medData['cantidad'],
                'unidad' => $medData['unidad'],
                'codigo_barras' => '750' . rand(10000000, 99999999),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // E. Mapear en medicamento_fuentes si la API retornó un ID de etiqueta (Set ID)
            if (!empty($fdaData['set_id'])) {
                DB::table('medicamento_fuentes')->updateOrInsert(
                    [
                        'fuente_id' => $fuente->id,
                        'identificador_externo' => $fdaData['set_id'],
                    ],
                    [
                        'catalogo_medicamento_id' => $catalogoId,
                        'fecha_consulta' => now(),
                        'version_fuente' => 'openFDA-v1',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            $procesados++;
            usleep(200000); // Pausa de 0.2s para evitar saturar el API rate limit
        }

        // 3. Finalizar registro de sincronización
        DB::table('sincronizaciones_vademecum')->where('id', $syncId)->update([
            'estado' => 'correcta',
            'registros_procesados' => $procesados,
            'detalle' => "Se procesaron {$procesados} principios activos exitosamente desde openFDA.",
            'fecha_fin' => now(),
        ]);

        $this->command->info("¡Seeder ejecutado con éxito! {$procesados} medicamentos importados con ficha clínica.");
    }

    protected function consultarOpenFDA(string $ingredient): array
    {
        try {
            $response = Http::get('https://api.fda.gov/drug/label.json', [
                'search' => 'active_ingredient:"' . $ingredient . '"',
                'limit' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json()['results'][0] ?? [];

                return [
                    'set_id' => $result['set_id'] ?? null,
                    'indicaciones' => $this->limpiarTexto($result['indications_and_usage'] ?? null),
                    'dosificacion' => $this->limpiarTexto($result['dosaging_and_administration'] ?? $result['dosage_and_administration'] ?? null),
                    'contraindicaciones' => $this->limpiarTexto($result['contraindications'] ?? null),
                    'advertencias' => $this->limpiarTexto($result['warnings'] ?? $result['warnings_and_cautions'] ?? null),
                ];
            }
        } catch (\Exception $e) {
            $this->command->warn("Error al consultar openFDA para: {$ingredient}. Error: " . $e->getMessage());
        }

        return [];
    }

    protected function limpiarTexto($campo): ?string
    {
        if (is_array($campo)) {
            $texto = implode("\n\n", $campo);
        } else {
            $texto = (string) $campo;
        }

        return Str::limit(trim($texto), 3000);
    }
}