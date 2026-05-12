<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run()
    {
        $areas = [
            ['nombre_area' => 'ALMACEN'],
            ['nombre_area' => 'SALA DE PARTO'],
            ['nombre_area' => 'QUIROFANO'],
            ['nombre_area' => 'EMERGENCIA ADULTO'],
            ['nombre_area' => 'EMERGENCIA PEDIATRICA'],
            ['nombre_area' => 'PEDIATRIA PISO'],
            ['nombre_area' => 'MEDICINA INTERNA'],
            ['nombre_area' => 'CIRUGIA'],
            ['nombre_area' => 'MATERNIDAD'],
            ['nombre_area' => 'TRAUMATOLOGIA'],
            ['nombre_area' => 'BANCO DE SANGRE Y LABORATORIO'],
            ['nombre_area' => 'ODONTOLOGIA'],
            ['nombre_area' => 'EPIDEMIOLOGIA'],
            ['nombre_area' => 'SANEAMIENTO'],
            ['nombre_area' => 'MANTENIMIENTO'],
            ['nombre_area' => 'COCINA'],
            ['nombre_area' => 'SEGURIDAD'],
            ['nombre_area' => 'DONACIONES'],
            ['nombre_area' => 'SUMINISTRO'],
            ['nombre_area' => 'TRASPASO'],
        ];

        foreach ($areas as $area) {
            DB::table('areas')->updateOrInsert(
                ['nombre_area' => $area['nombre_area']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}