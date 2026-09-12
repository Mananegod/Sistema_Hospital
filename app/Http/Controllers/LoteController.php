<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteController extends Controller
{
    /**
     * Muestra la lista principal de lotes con filtros por estado y trazabilidad.
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado');
        $search = trim($request->get('search', ''));

        $lotes = DB::table('lotes')
            ->when($estado, function ($query, $estado) {
                return $query->where('estado', $estado);
            })
            ->when($search, function ($query, $search) {
                return $query->where(DB::raw('LOWER(codigo_lote)'), 'like', '%' . strtolower($search) . '%')
                             ->orWhere(DB::raw('LOWER(proveedor)'), 'like', '%' . strtolower($search) . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Conteo general para tarjetas informativas
        $totalLotes = DB::table('lotes')->count();
        $lotesDefectuosos = DB::table('lotes')->where('estado', 'Defectuoso / Retirado')->count();
        $lotesCuarentena = DB::table('lotes')->where('estado', 'En Cuarentena')->count();

        return view('almacen.lotes.index', compact(
            'lotes', 
            'totalLotes', 
            'lotesDefectuosos', 
            'lotesCuarentena', 
            'estado', 
            'search'
        ));
    }

    /**
     * Muestra el detalle completo de un lote y todos los productos vinculados.
     */
    public function show($id)
    {
        $lote = DB::table('lotes')->where('id', $id)->first();

        if (!$lote) {
            return redirect()->route('lotes.index')
                ->with('error', 'El lote consultado no existe.');
        }

        // Medicamentos asociados
        $medicamentos = DB::table('medicamentos')
            ->where('lote_id', $lote->id)
            ->orWhere(DB::raw('LOWER(codigo_lote)'), '=', strtolower($lote->codigo_lote))
            ->get();

        // Insumos médicos asociados
        $insumos = DB::table('insumos_medicos')
            ->where('lote_id', $lote->id)
            ->orWhere(DB::raw('LOWER(codigo_lote)'), '=', strtolower($lote->codigo_lote))
            ->get();

        return view('almacen.lotes.show', compact('lote', 'medicamentos', 'insumos'));
    }

    /**
     * Actualiza el estado sanitario o proveedor de un lote.
     */
    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado'        => 'required|in:Disponible,En Cuarentena,Defectuoso / Retirado',
            'observaciones' => 'nullable|string|max:1000',
            'proveedor'     => 'nullable|string|max:255',
        ]);

        $lote = DB::table('lotes')->where('id', $id)->first();

        if (!$lote) {
            return back()->with('error', 'El lote no existe.');
        }

        DB::table('lotes')
            ->where('id', $id)
            ->update([
                'estado'        => $request->estado,
                'observaciones' => $request->observaciones,
                'proveedor'     => $request->proveedor ?? $lote->proveedor,
                'updated_at'    => now(),
            ]);

        $mensaje = "El lote {$lote->codigo_lote} ha sido actualizado a estado: {$request->estado}";

        return back()->with('success', $mensaje);
    }
}