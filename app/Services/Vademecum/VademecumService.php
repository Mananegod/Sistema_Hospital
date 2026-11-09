<?php

namespace App\Services\Vademecum;

use Illuminate\Support\Facades\Log;

class VademecumService
{
    private array $proveedores;

    public function __construct(array $proveedores)
    {
        $this->proveedores = $proveedores;
    }

    public function buscarEnTodasLasFuentes(string $termino): array
    {
        $resultadosUnificados = [];

        foreach ($this->proveedores as $proveedor) {
            try {
                $datos = $proveedor->buscar($termino);
                foreach ($datos as $medicamento) {
                    $resultadosUnificados[] = $medicamento;
                }
            } catch (\Throwable $e) {
                Log::error("Error consultando proveedor Vademécum: " . $e->getMessage());
            }
        }

        return $resultadosUnificados;
    }
}