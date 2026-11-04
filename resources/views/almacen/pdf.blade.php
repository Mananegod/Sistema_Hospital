<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        
        /* Estilos del Membrete */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .header-table td { vertical-align: middle; padding: 0; }
        
        .header-title { text-align: center; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        
        .table-data { width: 100%; border: 1px solid #000; border-collapse: collapse; margin-top: 10px; }
        .table-data th { background-color: #f2f2f2; border: 1px solid #000; padding: 6px; font-size: 9px; text-transform: uppercase; text-align: left; }
        .table-data td { border: 1px solid #000; padding: 5px 6px; font-size: 10px; vertical-align: middle; }
        
        .footer-text { text-align: center; font-size: 9px; color: #666; margin-top: 30px; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-transform: uppercase; font-size: 11px;">
            Imprimir / Guardar como PDF
        </button>
    </div>

    <!-- Tabla del Membrete Institucional -->
    <table class="header-table">
        <tr>
            <!-- Membrete 1 (Banderas) -->
            <td style="width: 30%; text-align: left;">
                @php
                    $path1 = public_path('membretes/membrete1.png');
                    $type1 = pathinfo($path1, PATHINFO_EXTENSION);
                    $data1 = file_exists($path1) ? file_get_contents($path1) : '';
                    $base641 = 'data:image/' . $type1 . ';base64,' . base64_encode($data1);
                @endphp
                <img src="{{ $base641 }}" alt="Membrete 1" style="max-height: 45px; width: auto; display: block;" />
            </td>

            <!-- Membrete 2 (Prosalud) + Texto Institucional -->
            <td style="width: 45%; text-align: left;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 35%; text-align: left; vertical-align: middle;">
                            @php
                                $path2 = public_path('membretes/membrete2.png');
                                $type2 = pathinfo($path2, PATHINFO_EXTENSION);
                                $data2 = file_exists($path2) ? file_get_contents($path2) : '';
                                $base642 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);
                            @endphp
                            <img src="{{ $base642 }}" alt="Membrete 2" style="max-height: 45px; width: auto; display: block;" />
                        </td>
                        <td style="width: 65%; vertical-align: middle; font-size: 7.5px; color: #6b7250; line-height: 1.2; font-weight: bold; padding-left: 5px;">
                            Instituto Autónomo de la Salud del Edo. Yaracuy<br>
                            Prosalud Yaracuy<br>
                            Hospital Dr. Tiburcio Garrido de Chivacoa
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Título del Reporte a la Derecha del Membrete -->
            <td class="header-title" style="width: 25%; text-align: right; font-size: 12px;">
                {{ $titulo }}<br>
                <span style="font-size: 8.5px; font-weight: normal; color: #555;">FECHA: {{ date('d/m/Y h:i A') }}</span>
            </td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th width="4%" style="text-align: center;">#</th>
                <th width="36%">DESCRIPCIÓN DEL ITEM</th>
                <th width="18%">TIPO DE INSUMO</th>
                <th width="12%" style="text-align: center;">LOTE</th>
                <th width="12%" style="text-align: center;">FECHA VENC.</th>
                <th width="9%" style="text-align: center;">MÍNIMO</th>
                <th width="9%" style="text-align: center;">STOCK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $nombre = $categoria === 'insumo' ? ($item->nombre_insumo ?? 'N/A') : ($item->nombre_medicamento ?? 'N/A');
                    $stock = $item->cantidad_stock ?? 0;
                    $vencimiento = !empty($item->fecha_vencimiento) ? date('d/m/Y', strtotime($item->fecha_vencimiento)) : 'N/A';
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td><strong>{{ $nombre }}</strong></td>
                    <td>{{ $item->tipo_insumo ?? 'Por Determinar' }}</td>
                    <td style="text-align: center;">{{ $item->codigo_lote ?? 'S/L' }}</td>
                    <td style="text-align: center;">{{ $vencimiento }}</td>
                    <td style="text-align: center;">{{ $item->stock_minimo ?? 0 }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $stock }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No se encontraron registros en esta área.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-text">
        Formato Oficial de Control de Inventario y Bienes Nacionales — Hospital Dr. Tiburcio Garrido
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>