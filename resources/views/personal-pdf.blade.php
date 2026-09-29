<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General de Personal</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 20px; }
        h1 { font-size: 20px; margin-bottom: 5px; text-transform: uppercase; }
        p { font-size: 12px; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 12px; }
        th { background-color: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 11px; }
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; font-size: 10px; font-weight: bold; border-radius: 4px; text-transform: uppercase; }
        .badge-admin { background-color: #f3e8ff; color: #6b21a8; }
        .badge-usuario { background-color: #f1f5f9; color: #475569; }
        .badge-activo { background-color: #d1fae5; color: #065f46; }
        .badge-inactivo { background-color: #f1f5f9; color: #64748b; }
        @media print {
            body { padding: 0; }
            no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <h1>Reporte General de Personal</h1>
    <p>Listado completo de Administradores y Usuarios registrados en el sistema - Fecha: {{ date('d/m/Y H:i') }}</p>

    <table>
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