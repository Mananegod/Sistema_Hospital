<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Excepción - Medicamento Vencido</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #1e293b; line-height: 1.6; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 25px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; color: #0f172a; }
        .header p { margin: 3px 0; font-size: 11px; color: #64748b; text-transform: uppercase; }
        .title { text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 25px; text-decoration: underline; color: #dc2626; }
        .content { margin-bottom: 25px; text-align: justify; }
        .box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
        .box table { width: 100%; border-collapse: collapse; }
        .box td { padding: 6px; font-size: 12px; }
        .signatures { margin-top: 80px; width: 100%; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; padding: 0 40px; }
        .line { border-top: 1px solid #0f172a; margin-top: 60px; margin-bottom: 6px; }
        .warning { font-size: 11px; font-style: italic; color: #b91c1c; margin-top: 30px; text-align: center; }
        @media print {
            body { margin: 15px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h2>Hospital &quot;Dr. Tiburcio Garrido&quot;</h2>
        <p>Departamento de Farmacia y Vigilancia Epidemiológica</p>
    </div>

    <div class="title">ACTA DE EXCEPCIÓN Y RESPONSABILIDAD CLÍNICA POR RETIRO DE ÍTEM VENCIDO</div>

    <div class="content">
        <p>Por medio del presente documento, se deja constancia formal de que el área solicitante ha requerido la extracción y uso excepcional de un insumo médico o fármaco que registra fecha de caducidad cumplida en el sistema de inventario local. El uso de este material queda bajo estricta evaluación y responsabilidad del equipo médico tratante.</p>
    </div>

    <div class="box">
        <table>
            <tr>
                <td><strong>Tipo de Ítem:</strong> {{ ucfirst($tipo) }}</td>
                <td><strong>Cantidad Solicitada:</strong> {{ $cantidad }} unidades</td>
            </tr>
            <tr>
                <td><strong>Descripción:</strong> {{ $nombreItem }}</td>
                <td><strong>Área Destino:</strong> {{ $nombreArea }}</td>
            </tr>
            <tr>
                <td><strong>Código de Lote:</strong> {{ $codigoLote }}</td>
                <td><strong>Fecha de Vencimiento:</strong> <span style="color: #dc2626; font-weight: bold;">{{ $fechaVencimiento }}</span></td>
            </tr>
        </table>
    </div>

    <div class="content">
        <p><strong>Justificación y Normativa:</strong> El responsable del servicio clínico asume la potestad de validar las condiciones organolépticas y la necesidad terapéutica inaplazable de este suministro, eximiendo al departamento de almacén general de complicaciones derivadas exclusivamente de la vigencia del fármaco reseñado.</p>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="line"></div>
                <strong>Solicitante / Médico Tratante</strong><br>
                <span>Firma y Sello</span>
            </td>
            <td>
                <div class="line"></div>
                <strong>Jefe de Servicio / Farmacia</strong><br>
                <span>Firma y Autorización</span>
            </td>
        </tr>
    </table>

    <div class="warning">
        Este documento impreso es de carácter obligatorio y debe ser archivado físicamente en la coordinación de enfermería y farmacia del hospital.
    </div>

    <div style="text-align: center; margin-top: 30px;" class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0f172a; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Imprimir Acta</button>
    </div>

</body>
</html>