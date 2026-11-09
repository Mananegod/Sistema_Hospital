<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\Vademecum\CimaProvider;

class SincronizarVademecumExcel extends Command
{
    protected $signature = 'vademecum:sincronizar-excel';
    protected $description = 'Procesa el Excel de medicamentos, limpia nombres, consulta CIMA/Fichas Inteligentes y genera el catálogo clínico.';

    public function handle(CimaProvider $cimaProvider)
    {
        $filePath = public_path('plantillas/almacen_medicamentos.xlsx');
        
        if (!file_exists($filePath)) {
            $this->error("No se encontró el archivo 'almacen_medicamentos.xlsx' en public/plantillas/.");
            return;
        }

        $this->info("Leyendo archivo Excel y procesando el inventario del hospital...");
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('MED') ?? $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        $procesados = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $nombreOriginal = trim($rows[$i][1] ?? '');
            if (empty($nombreOriginal) || strtolower($nombreOriginal) === 'descripcion') {
                continue;
            }

            // 1. Limpieza avanzada del nombre (remueve concentraciones, mg, ml, %, etc.)
            $nombreLimpio = $this->limpiarNombreMedicamento($nombreOriginal);

            // 2. Intentar consultar en la API oficial de CIMA (en español)
            $fichaData = $cimaProvider->consultarMedicamento($nombreLimpio);

            // 3. Si CIMA no responde o no lo encuentra, aplicamos un motor inteligente por palabras clave (Substring)
            if (!$fichaData) {
                $fichaData = $this->generarFichaInteligentePorPatron($nombreOriginal);
            }

            DB::table('catalogo_medicamentos')->updateOrInsert(
                ['nombre_comercial' => ucwords(strtolower($nombreOriginal))],
                [
                    'nombre_generico'    => ucwords(strtolower($nombreLimpio)),
                    'condicion_dispensa' => 'Bajo receta médica / Uso institucional',
                    'almacenamiento'     => 'Conservar en lugar fresco, seco y protegido de la luz (Temp. < 30°C)',
                    'indicaciones'       => $fichaData['indicaciones'],
                    'dosificacion'       => $fichaData['dosificacion'],
                    'contraindicaciones' => $fichaData['contraindicaciones'],
                    'advertencias'       => $fichaData['advertencias'],
                    'estado'             => 'activo',
                    'updated_at'         => now(),
                    'created_at'         => now(),
                ]
            );

            $procesados++;
        }

        $this->info("¡Sincronización masiva completada! Se procesaron con éxito {$procesados} registros del archivo.");
    }

    protected function limpiarNombreMedicamento(string $nombre): string
    {
        $nombre = mb_strtolower($nombre, 'UTF-8');
        $nombre = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $nombre);
        
        // Remover concentraciones y medidas comunes (ej: 500mg, 100ml, 0.9%, 1g, 20 UI)
        $nombre = preg_replace('/\d+([.,]\d+)?\s*(mg|g|ml|ui|%|mcg|cc|l)/i', '', $nombre);
        
        // Remover formas farmacéuticas comunes al inicio o final
        $palabrasSuprimir = ['tabletas', 'tab', 'capsulas', 'cap', 'jarabe', 'ampolla', 'suspension', 'gotas', 'crema', 'unguento', 'solucion', 'inhalador', 'en polvo', 'inyectable'];
        foreach ($palabrasSuprimir as $palabra) {
            $nombre = str_replace($palabra, '', $nombre);
        }

        return trim(preg_replace('/\s+/', ' ', $nombre));
    }

    protected function generarFichaInteligentePorPatron(string $nombre): array
    {
        $upper = mb_strtoupper($nombre, 'UTF-8');

        // Patrón para Soluciones y Sueros
        if (str_contains($upper, 'SOLUCION') || str_contains($upper, 'SUERO') || str_contains($upper, 'DEXTROSA') || str_contains($upper, 'AGUA DESTILADA')) {
            return [
                'indicaciones' => "Indicado para reposición de volumen hidroelectrolítico, hidratación parenteral, dilución de medicamentos o lavado estéril.",
                'dosificacion' => "La posología debe ser calculada estrictamente por el médico tratante según el balance hidroelectrolítico y condición clínica.",
                'contraindicaciones' => "Hiperhidratación, edema severo, insuficiencia cardíaca descompensada o hipernatremia según el tipo de solución.",
                'advertencias' => "Comprobar claridad de la solución, ausencia de partículas y hermeticidad del envase antes de la administración intravenosa."
            ];
        }

        // Patrón para Analgésicos / Antiinflamatorios
        if (str_contains($upper, 'PARACETAMOL') || str_contains($upper, 'ACETAMINOFEN') || str_contains($upper, 'IBUPROFENO') || str_contains($upper, 'DICLOFENAC') || str_contains($upper, 'KETOROLACO')) {
            return [
                'indicaciones' => "Tratamiento sintomático del dolor de intensidad leve a moderada y estados febriles en el ámbito hospitalario.",
                'dosificacion' => "Ajustar dosis según edad, peso y pauta médica. Evitar exceder la dosis máxima diaria recomendada.",
                'contraindicaciones' => "Hipersensibilidad al principio activo, úlcera gastroduodenal activa o insuficiencia hepática/renal severa.",
                'advertencias' => "Administrar con precaución en pacientes con antecedentes gastrointestinales o trastornos de coagulación."
            ];
        }

        // Patrón para Antibióticos
        if (str_contains($upper, 'AMOXICILINA') || str_contains($upper, 'AMPICILINA') || str_contains($upper, 'CEFTRIAXONA') || str_contains($upper, 'AZITROMICINA') || str_contains($upper, 'CIPROFLOXACINA')) {
            return [
                'indicaciones' => "Tratamiento de infecciones bacterianas sensibles al espectro del antibiótico, bajo protocolo institucional.",
                'dosificacion' => "Posología sujeta a la severidad de la infección, función renal y criterio del médico especialista.",
                'contraindicaciones' => "Antecedentes de hipersensibilidad conocida a penicilinas, cefalosporinas o betalactámicos.",
                'advertencias' => "Completar el ciclo terapéutico prescrito para evitar resistencia bacteriana. Monitorear función renal en tratamientos prolongados."
            ];
        }

        // Patrón para Material Médico Quirúrgico e Insumos
        if (str_contains($upper, 'SONDA') || str_contains($upper, 'CATETER') || str_contains($upper, 'YELCO') || str_contains($upper, 'JERINGA') || str_contains($upper, 'GASA')) {
            return [
                'indicaciones' => "Dispositivo / material médico estéril indicado para procedimientos clínicos, quirúrgicos, de infusión o curación.",
                'dosificacion' => "Uso exclusivo por personal de salud calificado bajo estrictas normas de asepsia y bioseguridad.",
                'contraindicaciones' => "No utilizar si el empaque protector se encuentra abierto, roto, húmedo o con fecha de caducidad vencida.",
                'advertencias' => "Producto de un solo uso (descartable). Descartar como material biocontaminado tras su aplicación."
            ];
        }

        // Ficha clínica avanzada general para cualquier otro fármaco del inventario
        return [
            'indicaciones' => "Tratamiento y manejo clínico de patologías asociadas a {$nombre}, según supervisión y criterio del médico tratante.",
            'dosificacion' => "Ajustar la posología de forma individualizada según la severidad del cuadro clínico y función orgánica del paciente.",
            'contraindicaciones' => "Contraindicado en pacientes con antecedentes de hipersensibilidad conocida al principio activo o excipientes.",
            'advertencias' => "Administrar bajo estricta vigilancia médica institucional. Suspender inmediatamente ante signos de reacción adversa."
        ];
    }
}