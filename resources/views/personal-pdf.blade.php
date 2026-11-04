<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General de Personal</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
        
        /* Estilos del Header / Membrete */
        .header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        h1 { font-size: 18px; margin: 5px 0 0 0; text-transform: uppercase; text-align: center; color: #0f172a; }
        p { font-size: 10px; color: #64748b; margin: 2px 0 0 0; text-transform: uppercase; text-align: center; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 10px; }
        
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; font-size: 9px; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
        .badge-admin { background-color: #f3e8ff; color: #6b21a8; }
        .badge-usuario { background-color: #f1f5f9; color: #475569; }
        .badge-activo { background-color: #d1fae5; color: #065f46; }
        .badge-inactivo { background-color: #f1f5f9; color: #64748b; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <table class="header-table">
            <tr>
                <!-- Membrete 1 -->
                <td style="width: 28%; text-align: left; vertical-align: middle;">
                    @php
                        $path1 = public_path('membretes/membrete1.png');
                        $type1 = pathinfo($path1, PATHINFO_EXTENSION);
                        $data1 = file_exists($path1) ? file_get_contents($path1) : '';
                        $base641 = 'data:image/' . $type1 . ';base64,' . base64_encode($data1);
                    @endphp
                    <img src="{{ $base641 }}" alt="Membrete 1" style="max-height: 45px; width: auto; display: block;" />
                </td>

                <!-- Sello del Hospital + Texto (Icono a la izquierda, texto a la derecha) -->
                <td style="width: 50%; text-align: left; vertical-align: middle;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                <!-- Membrete 2 (Prosalud) -->
                <td style="width: 22%; text-align: left; vertical-align: middle;">
                    @php
                        $path2 = public_path('membretes/membrete2.png');
                        $type2 = pathinfo($path2, PATHINFO_EXTENSION);
                        $data2 = file_exists($path2) ? file_get_contents($path2) : '';
                        $base642 = 'data:image/' . $type2 . ';base64,' . base64_encode($data2);
                    @endphp
                    <img src="{{ $base642 }}" alt="Membrete 2" style="max-height: 45px; width: auto; display: block;" />
                </td>
                            <td style="width: 78%; vertical-align: middle; font-size: 8px; color: #6b7250; line-height: 1.2; font-weight: bold; padding-left: 5px;">
                                Instituto Autónomo de la Salud del Edo. Yaracuy<br>
                                Prosalud Yaracuy<br>
                                Hospital Dr. Tiburcio Garrido de Chivacoa
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <h1>Reporte General de Personal</h1>
        <p>Listado completo de Administradores y Usuarios registrados en el sistema - Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Apellidos y Nombres</th>
                <th>Cargo</th>
                <th>Tipo de Usuario</th>
                <th>Turno</th>
                <th>Teléfono</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personal as $p)
            <tr>
                <td>{{ $p->cedula }}</td>
                <td><strong>{{ $p->apellidos }}</strong>, {{ $p->nombres }}</td>
                <td>{{ $p->cargo }}</td>
                <td>
                    <span class="badge {{ $p->tipo_usuario === 'Admin' ? 'badge-admin' : 'badge-usuario' }}">
                        {{ $p->tipo_usuario }}
                    </span>
                </td>
                <td>{{ $p->turno }}</td>
                <td>{{ $p->telefono }}</td>
                <td class="text-center">
                    <span class="badge {{ $p->activo ? 'badge-activo' : 'badge-inactivo' }}">
                        {{ $p->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>