<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $sectores = [
            'BARRIO CENTRO',
            'GUATANQUIRE I',
            'GUATANQUIRE II',
            'PEGUAIMA / SAN ANTONIO DE PEGUAIMA',
            'BARRIO DANIEL CARIAS LIMA',
            'ANDRÉS BELLO',
            'POZO NUEVO',

            'URBANIZACIÓN MONSEÑOR VICENTE LAMBRUSCHINI',
            'URBANIZACIÓN TRICENTENARIO',
            'URBANIZACIÓN SAN ANTONIO',
            'URBANIZACIÓN PUEBLO NUEVO',
            'URBANIZACIÓN MONTE OSCURO',
            'LAS PALMAS',

            '23 DE ENERO (SECTOR I)',
            '23 DE ENERO (SECTOR II)',
            'LA PEÑITA I',
            'LA PEÑITA II',
            'LOS BOLIVARIANOS',
            'JOSÉ FÉLIX RIBAS (SECTOR 1)',
            'JOSÉ FÉLIX RIBAS (SECTOR 2)',
            'EZEQUIEL ZAMORA',
            'GUAICAIPURO / MIGUEL GÓMEZ BICENTENARIO',
            'LA LIBERTAD / VILLA ZAZARIVACOA',
            'EL ESTADIUM',
            'WILLIAM LARA',
            'ALÍ PRIMERA',
            'LA CAÑADA / EL CALICHE',
            'MAMA PANCHA / TAMARINDO II',

            'CAÑAVERAL',
            'CUMARIPA / RIVERAS DE CUMARIPA / FUNDO CUMARIPA',
            'LAS COROZAS / LAS DELICIAS / SARARE',
            'SAN ANDRÉS',
            'LOS HORCONES',
            'LAS MULITAS Y EL TIAMAL',
            'PALO VERDE (RENACER DE PALO VERDE)',
            'SAN RAMÓN / ZENÓN ARIAS',
            'LA MANGA',
            'SANTA CATALINA DE ALEJANDRÍA / CAJA DE AGUA',
        ];

        foreach ($sectores as $sector) {
            DB::table('sectores')->updateOrInsert(
                ['nombre_sector' => $sector],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}