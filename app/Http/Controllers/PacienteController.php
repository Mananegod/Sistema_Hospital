<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Auditoria;

class PacienteController extends Controller
{
    private function formatearCedulaPdf(?string $cedula): string
    {
        if (!$cedula) return '';
        return number_format((int) $cedula, 0, ',', '.');
    }

    private function calcularEdadTexto(?string $fechaNacimiento): string
    {
        if (!$fechaNacimiento) return 'N/A';
        $nacimiento = Carbon::parse($fechaNacimiento);
        $hoy = Carbon::now();

        $anos = $nacimiento->diffInYears($hoy);
        $meses = $nacimiento->copy()->addYears($anos)->diffInMonths($hoy);
        $dias = $nacimiento->copy()->addYears($anos)->addMonths($meses)->diffInDays($hoy);

        if ($anos > 0) {
            return $anos . ($anos == 1 ? ' año' : ' años');
        } elseif ($meses > 0) {
            return $meses . ($meses == 1 ? ' mes' : ' meses');
        } else {
            return $dias . ($dias == 1 ? ' día' : ' días');
        }
    }

    public function index()
    {
        $areas = DB::table('areas')->orderBy('nombre_area')->get();

        $pacientes = DB::table('pacientes_internados')
            ->leftJoin('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select('pacientes_internados.*', 'areas.nombre_area as servicio', 'pacientes_internados.nombre_apellido as nombre')
            ->orderByDesc('pacientes_internados.created_at')
            ->get()
            ->map(function ($paciente_item) {
                $paciente_item->fecha_formateada = $paciente_item->fecha_ingreso 
                    ? Carbon::parse($paciente_item->fecha_ingreso)->format('d/m/Y') 
                    : '';
                
                if ($paciente_item->cedula && is_numeric($paciente_item->cedula)) {
                    $paciente_item->cedula_formateada = number_format((int) $paciente_item->cedula, 0, ',', '.');
                } else {
                    $paciente_item->cedula_formateada = $paciente_item->cedula;
                }

                if ($paciente_item->cedula_tutor && is_numeric($paciente_item->cedula_tutor)) {
                    $paciente_item->cedula_tutor_formateada = number_format((int) $paciente_item->cedula_tutor, 0, ',', '.');
                } else {
                    $paciente_item->cedula_tutor_formateada = $paciente_item->cedula_tutor;
                }

                return $paciente_item;
            });

        return view('pacientes', compact('areas', 'pacientes'));
    }

    public function verificarDocumento(Request $request)
    {
        $tipo = strtoupper(trim((string) $request->query('tipo_documento')));
        $documento = strtoupper(preg_replace('/\s+/', '', (string) $request->query('cedula')));
        $nombre = trim((string) $request->query('nombre_apellido'));
        $fechaNacimiento = $request->query('fecha_nacimiento');

        if (!in_array($tipo, ['V', 'E', 'P', 'SC'], true)) {
            return response()->json(['encontrado' => false, 'mensaje' => 'Tipo de identificación no válido.'], 422);
        }

        $base = DB::table('pacientes_internados')
            ->leftJoin('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select(
                'pacientes_internados.id',
                'pacientes_internados.tipo_documento',
                'pacientes_internados.cedula',
                'pacientes_internados.cedula_tutor',
                'pacientes_internados.nombre_apellido',
                'pacientes_internados.edad',
                'pacientes_internados.fecha_nacimiento',
                'pacientes_internados.genero',
                'pacientes_internados.area_id',
                'pacientes_internados.diagnostico',
                'pacientes_internados.tratamiento',
                'pacientes_internados.fecha_ingreso',
                'areas.nombre_area as servicio'
            );

        if ($tipo === 'SC') {
            if ($nombre === '' || !$fechaNacimiento) {
                return response()->json(['encontrado' => false, 'posibles' => []]);
            }

            $coincidencias = $base
                ->where('pacientes_internados.tipo_documento', 'SC')
                ->whereRaw('LOWER(pacientes_internados.nombre_apellido) = ?', [mb_strtolower($nombre)])
                ->where('pacientes_internados.fecha_nacimiento', $fechaNacimiento)
                ->orderByDesc('pacientes_internados.created_at')
                ->limit(5)
                ->get();

            return response()->json([
                'encontrado' => false,
                'posibles' => $coincidencias,
                'mensaje' => $coincidencias->isNotEmpty()
                    ? 'Existen registros con el mismo nombre y fecha de nacimiento. Verifique que no sea el mismo niño.'
                    : null,
            ]);
        }

        if ($documento === '') {
            return response()->json(['encontrado' => false]);
        }

        $paciente = $base
            ->where('pacientes_internados.tipo_documento', $tipo)
            ->where('pacientes_internados.cedula', $documento)
            ->first();

        return response()->json([
            'encontrado' => (bool) $paciente,
            'paciente' => $paciente,
            'mensaje' => $paciente
                ? 'Este documento ya está asociado a un paciente registrado.'
                : 'No se encontró un paciente con este documento.',
        ]);
    }

    public function store(Request $request)
    {
        $tipo = strtoupper((string) $request->input('tipo_documento', 'V'));
        
        if ($tipo === 'SC') {
            $cedula = null;
            $cedulaTutor = preg_replace('/[^0-9]/', '', (string) $request->input('cedula_tutor', ''));
        } else {
            $cedula = strtoupper(preg_replace('/\s+/', '', (string) $request->input('cedula', '')));
            $cedulaTutor = ''; 
        }
        
        $request->merge([
            'tipo_documento' => $tipo,
            'cedula' => $cedula,
            'cedula_tutor' => $cedulaTutor,
        ]);

        $cedulaRules = [
            Rule::requiredIf(in_array($tipo, ['V', 'E', 'P'], true)),
            'nullable',
            'string',
            'max:20',
            function ($attribute, $value, $fail) use ($tipo) {
                if ($value === null || $value === '') return;
                if (in_array($tipo, ['V', 'E'], true) && !preg_match('/^[0-9]{6,10}$/', $value)) {
                    $fail('La cédula venezolana debe contener entre 6 y 10 dígitos.');
                }
                if ($tipo === 'P' && !preg_match('/^[A-Z0-9]{5,20}$/', $value)) {
                    $fail('El pasaporte debe contener entre 5 y 20 caracteres alfanuméricos.');
                }
            },
        ];

        if ($tipo !== 'SC' && $cedula) {
            $cedulaRules[] = Rule::unique('pacientes_internados', 'cedula')
                ->where(fn ($q) => $q->where('tipo_documento', $tipo));
        }

        $validator = Validator::make($request->all(), [
            'tipo_documento' => ['required', Rule::in(['V', 'E', 'P', 'SC'])],
            'cedula' => $cedulaRules,
            'cedula_tutor' => [
                'nullable',
                function ($attribute, $value, $fail) use ($tipo) {
                    if ($tipo === 'SC') {
                        if (empty($value)) {
                            $fail('Debe indicar la cédula del tutor legal.');
                        } elseif (!preg_match('/^[0-9]{6,10}$/', $value)) {
                            $fail('La cédula del tutor debe contener entre 6 y 10 dígitos numéricos.');
                        }
                    }
                }
            ],
            'nombre_apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.-]+$/u'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'genero' => ['required', Rule::in(['Masculino', 'Femenino', 'Otro'])],
            'area_id' => ['required', 'exists:areas,id'],
            'diagnostico' => ['required', 'string', 'max:500'],
            'tratamiento' => ['nullable', 'string'],
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:2036-12-31'],
        ], [
            'cedula.required' => 'Debe indicar la cédula o el número de pasaporte.',
            'cedula.unique' => 'Ya existe un paciente registrado con este tipo y número de documento.',
            'nombre_apellido.regex' => 'El nombre solo debe contener letras, espacios, apóstrofes, puntos o guiones.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
            'area_id.exists' => 'El servicio o área seleccionada no es válido.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior al 31/12/2036.',
        ]);

        $edadCalculada = Carbon::parse($request->fecha_nacimiento)->age;

        $validator->after(function ($validator) use ($tipo, $edadCalculada) {
            if ($tipo === 'SC' && $edadCalculada > 12) {
                $validator->errors()->add('tipo_documento', 'La opción Sin Cédula está destinada exclusivamente a niños menores de 13 años.');
            }
            if ($tipo !== 'SC' && $edadCalculada < 13) {
                $validator->errors()->add('tipo_documento', 'Los pacientes menores sin cedula deben registrarse bajo la opción "Sin cédula / Usar Tutor".');
            }
        });

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $id = DB::table('pacientes_internados')->insertGetId([
            'tipo_documento' => $tipo,
            'cedula' => $cedula,
            'cedula_tutor' => $cedulaTutor,
            'nombre_apellido' => trim($request->nombre_apellido),
            'edad' => $edadCalculada,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'genero' => $request->genero,
            'area_id' => $request->area_id,
            'diagnostico' => $request->diagnostico,
            'tratamiento' => $request->tratamiento,
            'fecha_ingreso' => $request->fecha_ingreso,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Auditoria::create([
            'modulo' => 'Pacientes',
            'accion' => 'Registro',
            'descripcion' => "Se registró e internó al paciente: {$request->nombre_apellido}",
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Paciente internado y registrado correctamente.']);
        }

        return back()->with('success', 'Paciente internado y registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $tipo = strtoupper((string) $request->input('tipo_documento', 'V'));
        
        if ($tipo === 'SC') {
            $cedula = null;
            $cedulaTutor = preg_replace('/[^0-9]/', '', (string) $request->input('cedula_tutor', ''));
        } else {
            $cedula = strtoupper(preg_replace('/\s+/', '', (string) $request->input('cedula', '')));
            $cedulaTutor = ''; 
        }
        
        $request->merge([
            'tipo_documento' => $tipo,
            'cedula' => $cedula,
            'cedula_tutor' => $cedulaTutor,
        ]);

        $cedulaRules = [
            Rule::requiredIf(in_array($tipo, ['V', 'E', 'P'], true)),
            'nullable',
            'string',
            'max:20',
            function ($attribute, $value, $fail) use ($tipo) {
                if ($value === null || $value === '') return;
                if (in_array($tipo, ['V', 'E'], true) && !preg_match('/^[0-9]{6,10}$/', $value)) {
                    $fail('La cédula debe contener entre 6 y 10 dígitos.');
                }
                if ($tipo === 'P' && !preg_match('/^[A-Z0-9]{5,20}$/', $value)) {
                    $fail('El pasaporte debe contener entre 5 y 20 caracteres alfanuméricos.');
                }
            },
        ];

        if ($tipo !== 'SC' && $cedula) {
            $cedulaRules[] = Rule::unique('pacientes_internados', 'cedula')
                ->where(fn ($q) => $q->where('tipo_documento', $tipo))
                ->ignore($id);
        }

        $validator = Validator::make($request->all(), [
            'tipo_documento' => ['required', Rule::in(['V', 'E', 'P', 'SC'])],
            'cedula' => $cedulaRules,
            'cedula_tutor' => [
                'nullable',
                function ($attribute, $value, $fail) use ($tipo) {
                    if ($tipo === 'SC') {
                        if (empty($value)) {
                            $fail('Debe indicar la cédula del tutor legal.');
                        } elseif (!preg_match('/^[0-9]{6,10}$/', $value)) {
                            $fail('La cédula del tutor debe contener entre 6 y 10 dígitos numéricos.');
                        }
                    }
                }
            ],
            'nombre_apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'.-]+$/u'],
            'fecha_nacimiento' => ['required', 'date', 'before_or_equal:today'],
            'genero' => ['required', Rule::in(['Masculino', 'Femenino', 'Otro'])],
            'area_id' => ['required', 'exists:areas,id'],
            'diagnostico' => ['required', 'string', 'max:500'],
            'tratamiento' => ['nullable', 'string'],
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:2036-12-31'],
        ], [
            'cedula.unique' => 'Ya existe otro paciente con este tipo y número de documento.',
            'nombre_apellido.regex' => 'El nombre solo debe contener letras, espacios, apóstrofes, puntos o guiones.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser una fecha futura.',
        ]);

        $edadCalculada = Carbon::parse($request->fecha_nacimiento)->age;

        $validator->after(function ($validator) use ($tipo, $edadCalculada) {
            if ($tipo === 'SC' && $edadCalculada > 12) {
                $validator->errors()->add('tipo_documento', 'La opción Sin Cédula está destinada a niños menores sin cedula.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'No se pudieron validar los datos del paciente.');
        }

        try {
            $actualizado = DB::table('pacientes_internados')->where('id', $id)->update([
                'tipo_documento' => $tipo,
                'cedula' => $cedula,
                'cedula_tutor' => $cedulaTutor,
                'nombre_apellido' => trim($request->nombre_apellido),
                'edad' => $edadCalculada,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'area_id' => $request->area_id,
                'diagnostico' => $request->diagnostico,
                'tratamiento' => $request->tratamiento,
                'fecha_ingreso' => $request->fecha_ingreso,
                'updated_at' => now(),
            ]);

            if (!$actualizado && !DB::table('pacientes_internados')->where('id', $id)->exists()) {
                return back()->with('error', 'El paciente que intenta modificar no existe.');
            }

            Auditoria::create([
                'modulo' => 'Pacientes',
                'accion' => 'Edición',
                'descripcion' => "Se actualizaron los datos del paciente: {$request->nombre_apellido}",
            ]);

            return back()->with('success', 'Los datos del paciente se actualizaron correctamente.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Ocurrió un error al actualizar el paciente.');
        }
    }

    public function imprimirPdf($id)
    {
        return $this->generarPdf($id);
    }

    public function delete($id)
    {
        try {
            $paciente = DB::table('pacientes_internados')->where('id', $id)->first();

            if (!$paciente) {
                return back()->with('error', 'El registro del paciente no existe.');
            }

            DB::table('pacientes_internados')->where('id', $id)->delete();

            Auditoria::create([
                'modulo' => 'Pacientes',
                'accion' => 'Borrado',
                'descripcion' => "Se eliminó el registro clínico de: {$paciente->nombre_apellido}",
            ]);

            return back()->with('success', 'El registro del paciente fue eliminado correctamente.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'No se pudo eliminar el registro del paciente.');
        }
    }

public function generarPdf($id)
    {
        $paciente = DB::table('pacientes_internados')
            ->leftJoin('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select(
                'pacientes_internados.*', 
                'areas.nombre_area as servicio'
            )
            ->where('pacientes_internados.id', $id)
            ->first();

        if (!$paciente) {
            return back()->with('error', 'El registro del paciente no existe.');
        }

        Auditoria::create([
            'modulo' => 'Pacientes',
            'accion' => 'Reporte',
            'descripcion' => "Se generó el formato de impresión PDF del paciente: {$paciente->nombre_apellido}",
        ]);

        // Calculamos la edad exacta (meses, días o años) usando tu función existente
        $edadFormateadaPdf = $this->calcularEdadTexto($paciente->fecha_nacimiento);

        return view('pacientes.reporte-f15', compact('paciente', 'edadFormateadaPdf'));
    }
}