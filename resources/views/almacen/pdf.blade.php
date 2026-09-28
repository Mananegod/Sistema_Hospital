<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .institution { font-size: 10px; color: #444; }
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

    <table class="header-table">
        <tr>
            <td class="institution" width="45%">
                MINISTERIO DEL PODER POPULAR PARA LA SALUD<br>
                HOSPITAL GENERAL DR. TIBURCIO GARRIDO<br>
                CHIVACOA - ESTADO YARACUY
            </td>
            <td class="header-title" width="55%">
                {{ $titulo }}<br>
                <span style="font-size: 10px; font-weight: normal;">FECHA DE EMISIÓN: {{ date('d/m/Y h:i A') }}</span>
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