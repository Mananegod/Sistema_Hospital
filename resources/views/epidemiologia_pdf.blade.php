<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Epidemiológico - Hospital Dr. Tiburcio Garrido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
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
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
        }
        .summary-card .number {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
        }
        .summary-card .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f1f5f9;
            font-size: 9px;
            text-transform: uppercase;
            color: #475569;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-confirmado { background-color: #fee2e2; color: #dc2626; }
        .badge-espera { background-color: #fef3c7; color: #d97706; }
        .badge-sospechoso { background-color: #f1f5f9; color: #475569; }
        .no-print {
            margin-bottom: 15px;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background-color: #0f172a; color: #ffffff; border: none; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 11px; border-radius: 2px;">
            Imprimir / Guardar en PDF
        </button>
    </div>

    <div class="header">
        <h1>Hospital "Dr. Tiburcio Garrido"</h1>
        <p>Reporte Oficial de Vigilancia Epidemiológica - Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <table class="summary-grid">
        <tr>
            <td class="summary-card">
                <div class="number">{{ $totalCasos }}</div>
                <div class="label">Total Casos Registrados</div>
            </td>
            <td class="summary-card">
                <div class="number" style="color: #dc2626;">{{ $confirmados }}</div>
                <div class="label">Confirmados</div>
            </td>
            <td class="summary-card">
                <div class="number" style="color: #d97706;">{{ $enEspera }}</div>
                <div class="label">En Espera</div>
            </td>
            <td class="summary-card">
                <div class="number" style="color: #475569;">{{ $sospechosos }}</div>
                <div class="label">Sospechosos</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Paciente</th>
                <th>Cédula</th>
                <th>Sector</th>
                <th>Patología</th>
                <th>Inicio Síntomas</th>
                <th>Estado</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($casos as $index => $c)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $c->nombre_paciente }}</strong></td>
                    <td>{{ $c->cedula_paciente ?? 'N/T' }}</td>
                    <td>{{ $c->sector_procedencia }}</td>
                    <td><strong>{{ $c->patologia_cie10 }}</strong></td>
                    <td>{{ $c->fecha_sintomas ? date('d/m/Y', strtotime($c->fecha_sintomas)) : 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $c->estado_caso == 'CONFIRMADO' ? 'badge-confirmado' : ($c->estado_caso == 'EN ESPERA' ? 'badge-espera' : 'badge-sospechoso') }}">
                            {{ $c->estado_caso }}
                        </span>
                    </td>
                    <td>{{ $c->observaciones }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 15px; color: #94a3b8;">
                        No existen fichas ni alertas epidemiológicas registradas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>