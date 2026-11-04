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
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        /* Estilos para alinear los elementos del membrete horizontalmente */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .header-text {
            font-size: 9px;
            color: #6b7250; /* Tono caqui institucional similar al ejemplo */
            line-height: 1.3;
            font-weight: bold;
            padding-left: 8px;
        }
        .header h1 {
            margin: 5px 0 0 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #0f172a;
            text-align: center;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            text-align: center;
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
    <table class="header-table" style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <tr>
            <!-- Membrete 1 -->
            <td style="width: 30%; text-align: left; vertical-align: middle;">
                @php
                    $path1 = public_path('membretes/membrete1.png');
                    $type1 = pathinfo($path1, PATHINFO_EXTENSION);
                    $data1 = file_exists($path1) ? file_get_contents($path1) : '';
                    $base641 = 'data:image/' . $type1 . ';base64,' . base64_encode($data1);
                @endphp
                <img src="{{ $base641 }}" alt="Membrete 1" style="max-height: 45px; width: auto; display: block;" />
            </td>

            <!-- Membrete 2 + Texto Institucional -->
            <td style="width: 48%; text-align: left; vertical-align: middle;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 35%; text-align: left; vertical-align: middle;">
                            @php
                                $path2 = public_path('membretes/membrete2.png');
                                $type2 = pathinfo($path2, PATHINFO_EXTENSION);
                                $data2 = file_exists($path2) ? file_get_contents($path2) : '';
                                $base642 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);
                            @endphp
                        </td>
                        <td style="width: 65%; vertical-align: middle; font-size: 8px; color: #6b7250; line-height: 1.2; font-weight: bold; padding-left: 5px;">
                            Instituto Autónomo de la Salud del Edo. Yaracuy<br>
                            Prosalud Yaracuy<br>
                            Hospital Dr. Tiburcio Garrido de Chivacoa
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Sello del Hospital -->
            <td style="width: 22%; text-align: right; vertical-align: middle;">
                @php
                    $pathSello = public_path('membretes/sello_hospital.png');
                    $typeSello = pathinfo($pathSello, PATHINFO_EXTENSION);
                    $dataSello = file_exists($pathSello) ? file_get_contents($pathSello) : '';
                    $base64Sello = 'data:image/' . $typeSello . ';base64,' . base64_encode($dataSello);
                @endphp
                <img src="{{ $base642 }}" alt="Membrete 2" style="max-height: 45px; width: auto; display: block;" />
            </td>
        </tr>
    </table>

    <h1 style="margin: 5px 0 0 0; font-size: 16px; text-transform: uppercase; color: #0f172a; text-align: center;">Reporte Oficial de Vigilancia Epidemiológica</h1>
    <p style="margin: 2px 0 0 0; font-size: 9px; color: #64748b; text-transform: uppercase; text-align: center;">Generado el {{ date('d/m/Y H:i') }}</p>
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