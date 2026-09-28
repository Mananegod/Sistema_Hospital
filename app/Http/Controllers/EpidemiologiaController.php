<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EpidemiologiaController extends Controller
{
    public function index()
    {
        $casos = DB::table('casos_epidemiologicos')->orderBy('id', 'desc')->get();
        $sectores = DB::table('sectores')->orderBy('nombre_sector', 'asc')->get();
        
        $patologiasCasos = DB::table('casos_epidemiologicos')
            ->whereNotNull('patologia_cie10')
            ->distinct()
            ->pluck('patologia_cie10')
            ->toArray();
            
        $patologiasHistoricas = DB::table('canales_endemicos_historicos')
            ->distinct()
            ->pluck('patologia_cie10')
            ->toArray();
            
        $patologiasArray = array_unique(array_merge($patologiasCasos, $patologiasHistoricas));
        
        if (empty($patologiasArray)) {
            $patologiasArray = ['DENGUE', 'MALARIA', 'DIARREAS'];
        }
        $patologias = collect(array_values($patologiasArray));

        $anosCasos = [];
        foreach ($casos as $caso) {
            if ($caso->fecha_sintomas) {
                $anosCasos[] = Carbon::parse($caso->fecha_sintomas)->year;
            }
        }
        $anosHistoricos = DB::table('canales_endemicos_historicos')->distinct()->pluck('ano')->toArray();
        $anosDisponibles = array_unique(array_merge($anosCasos, $anosHistoricos));
        
        if (empty($anosDisponibles)) {
            $anosDisponibles = [intval(date('Y')), intval(date('Y')) - 1];
        }
        
        rsort($anosDisponibles);
        $anos = collect(array_values($anosDisponibles));
        
        // Agrupar casos previamente para evitar filtrados O(N^3)
        $casosAgrupados = [];
        foreach ($casos as $item) {
            if (!$item->fecha_sintomas || !$item->patologia_cie10) continue;
            $fecha = Carbon::parse($item->fecha_sintomas);
            $pat = strtoupper($item->patologia_cie10);
            $y = $fecha->year;
            $w = $fecha->weekOfYear;

            if ($w >= 1 && $w <= 52) {
                $casosAgrupados[$pat][$y][$w] = ($casosAgrupados[$pat][$y][$w] ?? 0) + 1;
            }
        }

        // Consultar todo el histórico de una sola vez
        $todosHistoricos = DB::table('canales_endemicos_historicos')->get()->groupBy(['patologia_cie10', 'ano']);

        $datosCanales = [];

        foreach ($patologias as $patologia) {
            $patUpper = strtoupper($patologia);
            foreach ($anos as $ano) {
                $historico = $todosHistoricos[$patologia][$ano] ?? collect();
                $medicamentosPrediccion = $this->obtenerMedicamentosPorPatologia($patologia);

                $exito = array_fill(0, 52, 0);
                $seguridad = array_fill(0, 52, 0);
                $alerta = array_fill(0, 52, 0);
                $epidemia = array_fill(0, 52, 0);
                $actual = array_fill(0, 52, 0);
                $personasRealesSemanales = array_fill(0, 52, 0);

                if ($historico->isNotEmpty()) {
                    for ($w = 1; $w <= 52; $w++) {
                        $idx = $w - 1;
                        $semData = $historico->firstWhere('semana', $w);
                        $exito[$idx] = $semData ? intval($semData->exito) : 0;
                        $seguridad[$idx] = $semData ? intval($semData->seguridad) : 0;
                        $alerta[$idx] = $semData ? intval($semData->alerta) : 0;
                        $epidemia[$idx] = $semData ? intval($semData->epidemia) : 0;
                        
                        $conteoRealCasos = $casosAgrupados[$patUpper][$ano][$w] ?? 0;

                        if ($semData && $semData->actual > 0) {
                            $actual[$idx] = intval($semData->actual);
                            $personasRealesSemanales[$idx] = intval($semData->actual) * 5; 
                        } else {
                            $personasRealesSemanales[$idx] = $conteoRealCasos;
                            $actual[$idx] = $conteoRealCasos > 0 ? round($conteoRealCasos / 5, 2) : 0;
                        }
                    }

                    $datosCanales[$patologia][$ano] = [
                        'actual' => $actual,
                        'exito' => $exito,
                        'seguridad' => $seguridad,
                        'alerta' => $alerta,
                        'epidemia' => $epidemia,
                        'conteo_real' => $personasRealesSemanales,
                        'medicamentos' => $medicamentosPrediccion,
                        'es_historico' => true
                    ];

                } else {
                    for ($w = 1; $w <= 52; $w++) {
                        $idx = $w - 1;
                        $factorEstacional = 1.2 + sin(($w / 52) * 2 * M_PI) * 0.8;
                        $promedioHistorico = max(2, round(5 * $factorEstacional));
                        $exito[$idx] = round($promedioHistorico * 0.5);
                        $seguridad[$idx] = round($promedioHistorico * 1.0);
                        $alerta[$idx] = round($promedioHistorico * 1.8);
                        $epidemia[$idx] = round($promedioHistorico * 2.8);

                        $personas = $casosAgrupados[$patUpper][$ano][$w] ?? 0;
                        $personasRealesSemanales[$idx] = $personas;
                        $actual[$idx] = $personas > 0 ? round($personas / 5, 2) : 0;
                    }

                    $datosCanales[$patologia][$ano] = [
                        'actual' => $actual,
                        'exito' => $exito,
                        'seguridad' => $seguridad,
                        'alerta' => $alerta,
                        'epidemia' => $epidemia,
                        'conteo_real' => $personasRealesSemanales,
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
            'archivo_csv' => 'required|file|max:5120',
        ], [
            'archivo_csv.required' => 'Por favor, selecciona un archivo.',
            'archivo_csv.max' => 'El archivo no puede pesar más de 5MB.'
        ]);

        $file = $request->file('archivo_csv');
        $path = $file->getRealPath();
        
        if (($handle = fopen($path, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            $canalesParaInsertar = [];

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($row) < 7) continue;

                $patologia = mb_strtoupper(trim($row[0]), 'UTF-8');
                $ano = intval(trim($row[1]));
                $semana = intval(trim($row[2]));
                $exito = intval(trim($row[3]));
                $seguridad = intval(trim($row[4]));
                $alerta = intval(trim($row[5]));
                $epidemia = intval(trim($row[6]));
                $actual = isset($row[7]) ? intval(trim($row[7])) : 0;

                if ($semana < 1 || $semana > 52 || $ano < 2000) continue;

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
            'cedula_paciente.max_digits'  => 'La cédula no puede ser mayor a 10 dígitos.',
            'cedula_paciente.numeric'     => 'La cédula debe contener strictly números, sin letras ni caracteres.',
            'patologia_cie10.required'    => 'La patología de notificación es obligatoria.',
            'sector_procedencia.required' => 'Debe seleccionar un sector de procedencia.',
            'fecha_sintomas.required'     => 'La fecha de inicio de síntomas es obligatoria.',
            'fecha_sintomas.date'         => 'La fecha de inicio de síntomas no es una fecha válida.',
            'fecha_sintomas.before_or_equal' => 'La fecha de inicio de síntomas no puede ser mayor al año 2036.',
            'estado_caso.required'        => 'Debe definir el estado actual del caso.',
            'observaciones.required'      => 'Es obligatorio incluir observaciones.',
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

public function pdf()
    {
        $casos = DB::table('casos_epidemiologicos')
            ->select(
                'id',
                'nombre_paciente',
                'cedula_paciente',
                'sector_procedencia',
                'patologia_cie10',
                'fecha_sintomas',
                'estado_caso',
                'observaciones',
                'created_at'
            )
            ->orderBy('fecha_sintomas', 'desc')
            ->get();

        $totalCasos  = $casos->count();
        $confirmados = $casos->where('estado_caso', 'CONFIRMADO')->count();
        $sospechosos = $casos->where('estado_caso', 'SOSPECHOSO')->count();
        $enEspera    = $casos->where('estado_caso', 'EN ESPERA')->count();

        // Intenta retornar la vista según donde esté guardada (igual a Retiros)
        if (view()->exists('epidemiologia_pdf')) {
            return view('epidemiologia_pdf', compact('casos', 'totalCasos', 'confirmados', 'sospechosos', 'enEspera'));
        }

        if (view()->exists('epidemiologia.pdf')) {
            return view('epidemiologia.pdf', compact('casos', 'totalCasos', 'confirmados', 'sospechosos', 'enEspera'));
        }

        return view('pdf.epidemiologia', compact('casos', 'totalCasos', 'confirmados', 'sospechosos', 'enEspera'));
    }
}