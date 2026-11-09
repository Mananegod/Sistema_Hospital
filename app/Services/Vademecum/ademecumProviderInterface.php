<?php

namespace App\Services\Vademecum;

interface VademecumProviderInterface
{
    /**
     * Busca medicamentos por término (nombre, principio activo, etc.)
     * 
     * @param string $termino
     * @return array
     */
    public function buscar(string $termino): array;

    /**
     * Consulta el detalle de un medicamento por su identificador externo único en la API.
     * 
     * @param string $idExterno
     * @return array|null
     */
    public function consultarPorId(string $idExterno): ?array;
}