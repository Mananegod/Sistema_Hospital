<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class PacienteController extends Controller
{
    public function index()
    {
        $areas = DB::table('areas')->get();

        $pacientes = DB::table('pacientes_internados')
            ->join('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select(
                'pacientes_internados.*',
                'areas.nombre_area as servicio'
            )
            ->whereDate('pacientes_internados.created_at', now()->toDateString())
            ->orderBy('pacientes_internados.created_at', 'desc')
            ->get();

        return view('pacientes', compact('areas', 'pacientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string|unique:pacientes_internados,cedula',
            'nombre_apellido' => 'required|string|max:255',
            'edad' => 'required|integer|min:0|max:120',
            'genero' => 'required|string|in:Masculino,Femenino,Otro',
            'area_id' => 'required',
            'diagnostico' => 'required|string',
            'tratamiento' => 'nullable|string',
            'fecha_ingreso' => 'required|date',
        ]);

        DB::table('pacientes_internados')->insert([
            'cedula' => $request->cedula,
            'nombre_apellido' => $request->nombre_apellido,
            'edad' => $request->edad,
            'genero' => $request->genero,
            'area_id' => $request->area_id,
            'diagnostico' => $request->diagnostico,
            'tratamiento' => $request->tratamiento,
            'fecha_ingreso' => $request->fecha_ingreso,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Paciente internado y registrado en el sistema correctamente.');
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cedula'           => 'required|regex:/^[0-9]+$/',
            'nombre_apellido'  => 'required|string|regex:/^[^0-9]+$/|max:255',
            'edad'             => 'required|integer|min:0|max:125',
            'genero'           => 'required|string|in:Masculino,Femenino,Otro',
            'area_id'          => 'required|exists:areas,id',
            'diagnostico'      => 'required|string|max:500',
            'tratamiento'      => 'nullable|string',
            'fecha_ingreso'    => 'required|date|before_or_equal:2036-12-31',
        ], [
            'cedula.regex'            => 'La cédula de identidad debe contener únicamente números.',
            'nombre_apellido.regex'   => 'El nombre y apellido no puede contener números.',
            'edad.integer'            => 'La edad del paciente debe ser un número entero.',
            'genero.in'               => 'El género seleccionado no es válido.',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser posterior al año 2036.',
            'area_id.exists'          => 'El servicio o área seleccionada no es válida.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Error en el formulario. Por favor revisa los campos.');
        }

        try {
            DB::table('pacientes_internados')
                ->where('id', $id)
                ->update([
                    'cedula'          => $request->cedula,
                    'nombre_apellido' => $request->nombre_apellido,
                    'edad'            => $request->edad,
                    'genero'          => $request->genero,
                    'area_id'         => $request->area_id,
                    'diagnostico'     => $request->diagnostico,
                    'tratamiento'     => $request->tratamiento,
                    'fecha_ingreso'   => $request->fecha_ingreso,
                    'updated_at'      => now(),
                ]);

            return back()->with('success', 'El expediente del paciente ha sido actualizado con éxito.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el paciente: ' . $e->getMessage());
        }
    }

    public function imprimirPdf($id)
    {
        $paciente = DB::table('pacientes_internados')
            ->join('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select('pacientes_internados.*', 'areas.nombre_area as servicio')
            ->where('pacientes_internados.id', $id)
            ->first();

        if (!$paciente) {
            return back()->with('error', 'El registro del paciente no existe.');
        }

        return view('pacientes.reporte-f15', compact('paciente'));
    }

    public function delete($id)
    {
        try {
            $existe = DB::table('pacientes_internados')->where('id', $id)->exists();

            if (!$existe) {
                return back()->with('error', 'El registro del paciente que intenta eliminar no existe.');
            }

            DB::table('pacientes_internados')->where('id', $id)->delete();

            return back()->with('success', 'El registro del paciente ha sido eliminado correctamente del sistema.');

        } catch (\Exception $e) {
            return back()->with('error', 'No se pudo eliminar el registro: ' . $e->getMessage());
        }
    }
    
    public function generarPdf($id)
    {
        $paciente = DB::table('pacientes_internados')
            ->join('areas', 'pacientes_internados.area_id', '=', 'areas.id')
            ->select('pacientes_internados.*', 'areas.nombre_area as servicio')
            ->where('pacientes_internados.id', $id)
            ->first();

        if (!$paciente) {
            return back()->with('error', 'El registro del paciente no existe.');
        }

        $html = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Formato F15 - Almacén</title>
            <style>
                body { font-family: "Helvetica", "Arial", sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
                .header-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .header-title { text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase; }
                .header-subtitle { text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 10px; }
                .institution { font-size: 10px; color: #555; }
                
                .info-box { width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 15px; }
                .info-box td { padding: 6px 8px; border: 1px solid #000; vertical-align: top; }
                .label { font-weight: bold; text-transform: uppercase; font-size: 9px; color: #111; display: block; margin-bottom: 2px; }
                .value { font-size: 11px; font-weight: normal; color: #000; }
                
                .treatment-table { width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 40px; }
                .treatment-table th { background-color: #f2f2f2; padding: 6px; border: 1px solid #000; font-size: 10px; text-transform: uppercase; text-align: left; }
                .treatment-table td { padding: 12px 8px; border: 1px solid #000; font-size: 11px; min-height: 120px; vertical-align: top; }
                
                .signatures-table { width: 100%; border-collapse: collapse; margin-top: 50px; page-break-inside: avoid; }
                .signatures-table td { width: 20%; text-align: center; vertical-align: bottom; font-size: 8px; padding: 5px; }
                .line { width: 90%; margin: 0 auto 5px auto; border-top: 1px solid #000; }
                .footer-text { text-align: center; font-size: 9px; color: #777; margin-top: 25px; }
            </style>
        </head>
        <body>
            <table class="header-table">
                <tr>
                    <td class="institution" width="40%">
                        MINISTERIO DE SALUD Y DESARROLLO SOCIAL<br>
                        HOSPITAL GENERAL DR. TIBURCIO GARRIDO<br>
                        CHIVACOA - ESTADO YARACUY
                    </td>
                    <td class="header-title" width="30%">HOJA DE SERVICIO<br>ALMACÉN</td>
                    <td style="text-align: right; font-size: 10px;" width="30%">
                        <strong>CONTRALOR DE EXISTENCIA</strong><br>
                        Nº COMPROB: ________________
                    </td>
                </tr>
            </table>

            <table class="info-box">
                <tr>
                    <td width="50%">
                        <span class="label">Servicio / Área Destinatario</span>
                        <div class="value">' . e($paciente->servicio) . '</div>
                    </td>
                    <td width="50%">
                        <span class="label">Fecha de Ingreso</span>
                        <div class="value">' . date('d/m/Y', strtotime($paciente->fecha_ingreso)) . '</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="label">Nombre y Apellido del Paciente</span>
                        <div class="value">' . e($paciente->nombre_apellido) . '</div>
                    </td>
                    <td>
                        <span class="label">Cédula / Edad / Género</span>
                        <div class="value">V- ' . e($paciente->cedula) . ' &nbsp;&nbsp;|&nbsp;&nbsp; ' . e($paciente->edad) . ' Años &nbsp;&nbsp;|&nbsp;&nbsp; ' . e($paciente->genero ?? 'N/A') . '</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span class="label">Diagnóstico Inicial (Dx)</span>
                        <div class="value">' . e($paciente->diagnostico) . '</div>
                    </td>
                </tr>
            </table>

            <table class="treatment-table">
                <thead>
                    <tr>
                        <th>Descripción del Tratamiento / Insumos Solicitados para el Paciente</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . nl2br(e($paciente->tratamiento ?? 'No se especificó tratamiento médico detallado en el registro.')) . '</td>
                    </tr>
                </tbody>
            </table>

            <table class="signatures-table">
                <tr>
                    <td>
                        <div class="line"></div>
                        JEFE DE SERVICIO<br>SOLICITADO
                    </td>
                    <td>
                        <div class="line"></div>
                        DIRECTOR<br>DE LA INDEPENDENCIA
                    </td>
                    <td>
                        <div class="line"></div>
                        ADMINISTRADOR<br>INTENDENTE
                    </td>
                    <td>
                        <div class="line"></div>
                        CONTRALOR DE<br>EXISTENCIA
                    </td>
                    <td>
                        <div class="line"></div>
                        JEFE DEL ALMACÉN<br>DESPACHADOR
                    </td>
                </tr>
            </table>

            <div class="footer-text">
                Formato Oficial de Control F15 - Repositorio de Control de Inventario y Bienes Nacionales
            </div>
        </body>
        </html>';

        $pdf = Pdf::loadHTML($html)->setPaper('letter', 'portrait');
        return $pdf->download('F15_Paciente_' . $paciente->cedula . '.pdf');
    }
}