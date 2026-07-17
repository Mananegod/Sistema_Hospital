<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Disease;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EpidemiologiaController extends Controller
{
    public function index()
    {
        $casos = DB::table('casos_epidemiologicos')->orderBy('id', 'desc')->get();
        $sectores = DB::table('sectores')->orderBy('nombre_sector', 'asc')->get();
        
        // 1. Obtener Patologías de ambas fuentes (Casos activos + Históricos importados)
        $patologiasCasos = DB::table('casos_epidemiologicos')->whereNotNull('patologia_cie10')->distinct()->pluck('patologia_cie10')->toArray();
        $patologiasHistoricas = DB::table('canales_endemicos_historicos')->distinct()->pluck('patologia_cie10')->toArray();
        $patologiasArray = array_unique(array_merge($patologiasCasos, $patologiasHistoricas));
        
        // Fallback si no existen patologías en BD
        if (empty($patologiasArray)) {
            $patologiasArray = ['DENGUE', 'MALARIA', 'DIARREAS'];
        }
        $patologias = collect($patologiasArray);

        // 2. Obtener Años disponibles de ambas fuentes
        $anosCasos = [];
        foreach ($casos as $caso) {
            if ($caso->fecha_sintomas) {
                $anosCasos[] = Carbon::parse($caso->fecha_sintomas)->year;
            }
        }
        $anosHistoricos = DB::table('canales_endemicos_historicos')->distinct()->pluck('ano')->toArray();
        $anosDisponibles = array_unique(array_merge($anosCasos, $anosHistoricos));
        
        // Fallback si no existen años en BD (Usamos año actual y el anterior)
        if (empty($anosDisponibles)) {
            $anosDisponibles = [intval(date('Y')), intval(date('Y')) - 1];
        }
        
        rsort($anosDisponibles); // Ordenar de mayor a menor
        $anos = collect($anosDisponibles);
        
        $datosCanales = [];

        // 3. CONSTRUCCIÓN DE CANALES ENDÉMICOS
        foreach ($patologias as $patologia) {
            foreach ($anos as $ano) {
                
                // Intentamos buscar si este año y patología tienen datos importados del Excel físico
                $historico = DB::table('canales_endemicos_historicos')
                    ->where('patologia_cie10', $patologia)
                    ->where('ano', $ano)
                    ->orderBy('semana', 'asc')
                    ->get();

                $medicamentosPrediccion = $this->obtenerMedicamentosPorPatologia($patologia);

                // Inicializamos un array para contar las personas físicas reales por semana (1 a 52)
                $personasRealesSemanales = array_fill(1, 52, 0);

                // Filtramos los casos activos en BD para esta patología y año
                $casosFiltrados = $casos->filter(function($item) use ($patologia, $ano) {
                    if (strtoupper($item->patologia_cie10) !== strtoupper($patologia)) return false;
                    return Carbon::parse($item->fecha_sintomas)->year == $ano;
                });

                // Contamos las personas una por una por semana
                foreach ($casosFiltrados as $item) {
                    $semana = Carbon::parse($item->fecha_sintomas)->weekOfYear; 
                    if ($semana >= 1 && $semana <= 52) {
                        $personasRealesSemanales[$semana]++;
                    }
                }

                if ($historico->isNotEmpty()) {
                    // SI EXISTE HISTÓRICO IMPORTADO DEL EXCEL: Usamos sus curvas reales y fijas
                    $exito = [];
                    $seguridad = [];
                    $alerta = [];
                    $epidemia = [];
                    $actual = [];

                    // Rellenamos semana a semana (1 a 52)
                    for ($w = 1; $w <= 52; $w++) {
                        $semData = $historico->firstWhere('semana', $w);
                        $exito[$w] = $semData ? $semData->exito : 0;
                        $seguridad[$w] = $semData ? $semData->seguridad : 0;
                        $alerta[$w] = $semData ? $semData->alerta : 0;
                        $epidemia[$w] = $semData ? $semData->epidemia : 0;
                        
                        // Si hay casos cargados del CSV, los respetamos. Si no, le asignamos el cálculo de personas/5
                        if ($semData && $semData->actual > 0) {
                            $actual[$w] = $semData->actual;
                            // Sincronizamos las personas reales en base al histórico si existía
                            $personasRealesSemanales[$w] = $semData->actual * 5; 
                        } else {
                            $actual[$w] = $personasRealesSemanales[$w] > 0 ? round($personasRealesSemanales[$w] / 5, 2) : 0;
                        }
                    }

                    $datosCanales[$patologia][$ano] = [
                        'actual' => array_values($actual),
                        'exito' => array_values($exito),
                        'seguridad' => array_values($seguridad),
                        'alerta' => array_values($alerta),
                        'epidemia' => array_values($epidemia),
                        'conteo_real' => array_values($personasRealesSemanales), // Pasamos las personas reales al JS
                        'medicamentos' => $medicamentosPrediccion,
                        'es_historico' => true
                    ];

                } else {
                    // SI NO EXISTE HISTÓRICO: Generamos curvas estacionales calculadas por el sistema
                    $exito = []; $seguridad = []; $alerta = []; $epidemia = []; $actual = [];
                    
                    for ($w = 1; $w <= 52; $w++) {
                        $factorEstacional = 1.2 + sin(($w / 52) * 2 * M_PI) * 0.8;
                        $promedioHistorico = max(2, round(5 * $factorEstacional));
                        $exito[$w] = round($promedioHistorico * 0.5);
                        $seguridad[$w] = round($promedioHistorico * 1.0);
                        $alerta[$w] = round($promedioHistorico * 1.8);
                        $epidemia[$w] = round($promedioHistorico * 2.8);

                        // Regla de Negocio: 1 caso en gráfico por cada 5 personas registradas
                        $personas = $personasRealesSemanales[$w];
                        $actual[$w] = $personas > 0 ? round($personas / 5, 2) : 0;
                    }

                    $datosCanales[$patologia][$ano] = [
                        'actual' => array_values($actual),
                        'exito' => array_values($exito),
                        'seguridad' => array_values($seguridad),
                        'alerta' => array_values($alerta),
                        'epidemia' => array_values($epidemia),
                        'conteo_real' => array_values($personasRealesSemanales), // Pasamos las personas reales al JS
                        'medicamentos' => $medicamentosPrediccion,
                        'es_historico' => false
                    ];
                }
            }
        }

        return view('epidemiologia', compact('casos', 'sectores', 'patologias', 'anos', 'datosCanales'));
    }


    public function importar(Request $request)
    {
        $request->validate([
            'archivo_csv' => 'required|file|max:5120', // Soporta archivos de hasta 5MB
        ], [
            'archivo_csv.required' => 'Por favor, selecciona un archivo.',
            'archivo_csv.max' => 'El archivo no puede pesar más de 5MB.'
        ]);

        $file = $request->file('archivo_csv');
        $path = $file->getRealPath();
        
        if (($handle = fopen($path, 'r')) !== FALSE) {
            // Cabecera: patologia,ano,semana,exito,seguridad,alerta,epidemia,actual
            $header = fgetcsv($handle, 1000, ",");
            
            $canalesParaInsertar = [];

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($row) < 7) {
                    continue; 
                }

                $patologia = mb_strtoupper(trim($row[0]), 'UTF-8');
                $ano = intval(trim($row[1]));
                $semana = intval(trim($row[2]));
                $exito = intval(trim($row[3]));
                $seguridad = intval(trim($row[4]));
                $alerta = intval(trim($row[5]));
                $epidemia = intval(trim($row[6]));
                $actual = isset($row[7]) ? intval(trim($row[7])) : 0;

                if ($semana < 1 || $semana > 52 || $ano < 2000) {
                    continue; 
                }

                $canalesParaInsertar[] = [
                    'patologia_cie10' => $patologia,
                    'ano'             => $ano,
                    'semana'          => $semana,
                    'exito'           => $exito,
                    'seguridad'       => $seguridad,
                    'alerta'          => $alerta,
                    'epidemia'        => $epidemia,
                    'actual'          => $actual,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
            fclose($handle);

            if (!empty($canalesParaInsertar)) {
                DB::transaction(function() use ($canalesParaInsertar) {
                    foreach ($canalesParaInsertar as $registro) {
                        DB::table('canales_endemicos_historicos')->updateOrInsert(
                            [
                                'patologia_cie10' => $registro['patologia_cie10'],
                                'ano'             => $registro['ano'],
                                'semana'          => $registro['semana']
                            ],
                            [
                                'exito'      => $registro['exito'],
                                'seguridad'  => $registro['seguridad'],
                                'alerta'     => $registro['alerta'],
                                'epidemia'   => $registro['epidemia'],
                                'actual'     => $registro['actual'],
                                'updated_at' => now()
                            ]
                        );
                    }
                });

                return redirect()->back()->with('success', '¡Histórico anual de canales endémicos importado correctamente!');
            }
        }

        return redirect()->back()->withErrors(['archivo_csv' => 'No se pudo leer el archivo de forma correcta o el formato es incorrecto.']);
    }

    private function obtenerMedicamentosPorPatologia($patologia)
    {
        $pat = strtolower($patologia);
        if (str_contains($pat, 'dengue')) {
            return [
                ['nombre' => 'ACETAMINOFEN 500 MG TAB', 'unidad' => 'Tabletas', 'indicacion' => '1 tableta cada 6 horas por fiebre.'],
                ['nombre' => 'SOLUCION FISIOLOGICA 0.9% 500ML', 'unidad' => 'Frascos', 'indicacion' => 'Hidratación endovenosa si hay signos de alarma.'],
                ['nombre' => 'SUERO ORAL EN POLVO', 'unidad' => 'Sobres', 'indicacion' => 'Disolver en 1 litro de agua de consumo continuo.']
            ];
        } elseif (str_contains($pat, 'diarrea') || str_contains($pat, 'amebiasis')) {
            return [
                ['nombre' => 'SUERO ORAL EN POLVO', 'unidad' => 'Sobres', 'indicacion' => 'Prevención de deshidratación por evacuaciones.'],
                ['nombre' => 'METRONIDAZOL 500 MG COMPRIMIDOS', 'unidad' => 'Comprimidos', 'indicacion' => 'Tratamiento antiparasitario bajo orden médica.'],
                ['nombre' => 'ZINC TABLETAS 20 MG', 'unidad' => 'Tabletas', 'indicacion' => 'Suplemento diario por 10 a 14 días.']
            ];
        } elseif (str_contains($pat, 'malaria') || str_contains($pat, 'paludismo')) {
            return [
                ['nombre' => 'PRIMAQUINA 15 MG TABLETAS', 'unidad' => 'Tabletas', 'indicacion' => 'Esquema de erradicación hepática.'],
                ['nombre' => 'CLOROQUINA FOSTATO 250 MG', 'unidad' => 'Tabletas', 'indicacion' => 'Tratamiento esquizonticida sanguíneo.']
            ];
        }

        return [
            ['nombre' => 'ACETAMINOFEN 500 MG TAB', 'unidad' => 'Tabletas', 'indicacion' => 'Manejo sintomático general.'],
            ['nombre' => 'SUERO ORAL EN POLVO', 'unidad' => 'Sobres', 'indicacion' => 'Soporte electrolítico preventivo.']
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_paciente'    => 'required|regex:/^[a-zA-ZñÑáéíóúÁÉÍÓÚ\s]+$/|max:25',
            'cedula_paciente'    => 'required|numeric|max_digits:10',
            'patologia_cie10'    => 'required',
            'sector_procedencia' => 'required', 
            'fecha_sintomas'     => 'required|date|before_or_equal:2036-12-31',
            'estado_caso'        => 'required|in:SOSPECHOSO,EN ESPERA,CONFIRMADO',
            'observaciones'      => 'required',
        ], [
            'nombre_paciente.required'    => 'El nombre del paciente es obligatorio.',
            'cedula_paciente.required'    => 'La cédula del paciente es obligatoria y se requiere algún dato.',
            'cedula_paciente.max_digits'    => 'La cedula no puede ser mayor a 10 digitos',
            'cedula_paciente.numeric'     => 'La cédula debe contener estrictamente números, sin letras ni caracteres.',
            'patologia_cie10.required'    => 'La patología de notificación es obligatoria.',
            'sector_procedencia.required' => 'Debe seleccionar un sector de procedencia.',
            'fecha_sintomas.required'     => 'La fecha de inicio de síntomas es obligatoria.',
            'fecha_sintomas.date'         => 'La fecha de inicio de síntomas no es una fecha válida.',
            'fecha_sintomas.before_or_equal' => 'La fecha de inicio de síntomas no puede ser mayor al año 2036.',
            'estado_caso.required'        => 'Debe definir el estado actual del caso.',
            'observaciones.required'        => 'Es obligatorio incluir observaciones',
        ]);

        $nombre = mb_strtoupper($request->nombre_paciente, 'UTF-8');
        $patologia = mb_strtoupper($request->patologia_cie10, 'UTF-8');

        DB::table('casos_epidemiologicos')->insert([
            'nombre_paciente'    => $nombre,
            'cedula_paciente'    => $request->cedula_paciente,
            'patologia_cie10'    => $patologia,
            'sector_procedencia' => $request->sector_procedencia, 
            'fecha_sintomas'     => $request->fecha_sintomas,
            'estado_caso'        => $request->estado_caso,
            'observaciones'      => $request->observaciones,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->back()->with('success', 'Caso epidemiológico registrado correctamente.');
    }

    public function destroy($id)
    {
        DB::table('casos_epidemiologicos')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Alerta eliminada del sistema.');
    }
}
class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = [
            // Vectores
            ['category' => 'Enfermedades Transmitidas por Vectores', 'name' => 'Dengue'],
            ['category' => 'Enfermedades Transmitidas por Vectores', 'name' => 'Malaria (Paludismo)'],
            ['category' => 'Enfermedades Transmitidas por Vectores', 'name' => 'Chikungunya'],
            ['category' => 'Enfermedades Transmitidas por Vectores', 'name' => 'Zika'],
            
            // Respiratorias
            ['category' => 'Enfermedades Respiratorias', 'name' => 'Infecciones Respiratorias Agudas (IRA)'],
            ['category' => 'Enfermedades Respiratorias', 'name' => 'Neumonías'],
            ['category' => 'Enfermedades Respiratorias', 'name' => 'Enfermedad tipo Influenza (ETI)'],
            
            // Gastrointestinales
            ['category' => 'Enfermedades Gastrointestinales', 'name' => 'Enfermedades Diarreicas Agudas (EDA)'],
            
            // Infecciosas Comunes
            ['category' => 'Enfermedades Infecciosas Comunes', 'name' => 'Varicela (lechina)'],
            ['category' => 'Enfermedades Infecciosas Comunes', 'name' => 'Parotiditis (paperas)'],
            
            // Zoonosis
            ['category' => 'Zoonosis', 'name' => 'Leptospirosis'],
            
            // OPCIÓN ESPECIAL
            ['category' => 'General', 'name' => 'Otra enfermedad']
        ];

        foreach ($diseases ?? [] as $disease) {
            Disease::create($disease);
        }
    }
}