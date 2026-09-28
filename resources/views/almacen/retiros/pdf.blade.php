<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Retiros - Hospital Dr. Tiburcio Garrido</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            color: #0f172a;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            color: #475569;
        }
        .text-center { text-align: center; }
        .badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .badge-medicamento { background-color: #ecfdf5; color: #047857; }
        .badge-insumo { background-color: #eff6ff; color: #1d4ed8; }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .signature-box {
            width: 40%;
            border-top: 1px solid #0f172a;
            padding-top: 5px;
            font-size: 10px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="background: #0f172a; color: #fff; border: none; padding: 8px 16px; cursor: pointer; border-radius: 4px; font-weight: bold;">
            Imprimir / Guardar como PDF
        </button>
    </div>

    <div class="header">
        <h1>Hospital Dr. Tiburcio Garrido</h1>
        <p>Reporte Oficial de Salidas y Retiros de Inventario</p>
    </div>

    <div class="meta-info">
        <div><strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i A') }}</div>
        <div><strong>Total de Registros:</strong> {{ isset($ultimosRetiros) ? count($ultimosRetiros) : 0 }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th>Ítem / Descripción</th>
                <th style="width: 15%;">Tipo</th>
                <th>Área Destino</th>
                <th class="text-center" style="width: 12%;">Cantidad</th>
                <th class="text-center" style="width: 20%;">Fecha y Hora</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ultimosRetiros ?? [] as $index => $retiro)
                @php
                    $r = is_array($retiro) ? (object) $retiro : $retiro;

                    // Nombre
                    $nombre = $r->nombre ?? $r->nombre_medicamento ?? $r->nombre_insumo ?? '';
                    if (empty($nombre) && is_object($retiro)) {
                        $nombre = $retiro->medicamento->nombre_medicamento ?? $retiro->insumo->nombre_insumo ?? '';
                    }
                    if (empty($nombre)) { $nombre = 'N/A'; }

                    // Tipo
                    $tipoExplicit = strtolower((string)($r->tipo_item ?? $r->tipo ?? ''));
                    if ($tipoExplicit === 'insumo' || !empty($r->insumo_id) || !empty($r->nombre_insumo)) {
                        $tipoTexto = 'Insumo';
                    } elseif ($tipoExplicit === 'medicamento' || !empty($r->medicamento_id) || !empty($r->nombre_medicamento)) {
                        $tipoTexto = 'Medicamento';
                    } else {
                        $esInsumo = false;
                        if (isset($todosLosInsumos) && !empty($nombre)) {
                            foreach ($todosLosInsumos as $ins) {
                                $insNombre = is_array($ins) ? ($ins['nombre_insumo'] ?? '') : ($ins->nombre_insumo ?? '');
                                if (strcasecmp(trim($insNombre), trim($nombre)) === 0) {
                                    $esInsumo = true;
                                    break;
                                }
                            }
                        }
                        $tipoTexto = $esInsumo ? 'Insumo' : 'Medicamento';
                    }

                    // Área
                    $nombreArea = $r->nombre_area ?? '';
                    if (empty($nombreArea) && is_object($retiro) && isset($retiro->area)) {
                        $nombreArea = $retiro->area->nombre_area ?? '';
                    }
                    if (empty($nombreArea)) { $nombreArea = 'Área general'; }

                    // Fecha
                    $fechaFormateada = '-';
                    if (!empty($r->created_at)) {
                        try {
                            $fechaFormateada = \Carbon\Carbon::parse($r->created_at)->format('d/m/Y h:i A');
                        } catch (\Exception $e) {
                            $fechaFormateada = (string)$r->created_at;
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $nombre }}</strong></td>
                    <td>
                        <span class="badge {{ strtolower($tipoTexto) === 'insumo' ? 'badge-insumo' : 'badge-medicamento' }}">
                            {{ $tipoTexto }}
                        </span>
                    </td>
                    <td>{{ $nombreArea }}</td>
                    <td class="text-center"><strong>{{ $r->cantidad ?? 0 }}</strong></td>
                    <td class="text-center">{{ $fechaFormateada }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No hay retiros registrados para este periodo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p><strong>Entregado Por (Almacén)</strong></p>
            <p style="margin-top: 30px;">Firma y Sello</p>
        </div>
        <div class="signature-box">
            <p><strong>Recibido Por (Área)</strong></p>
            <p style="margin-top: 30px;">Firma y Sello</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>