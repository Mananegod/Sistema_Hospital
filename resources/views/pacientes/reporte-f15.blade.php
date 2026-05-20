<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF - {{ $paciente->nombre_apellido }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap');
        
        .fuente-reporte {
            font-family: 'Courier Prime', monospace;
        }

        @media print {
            @page {
                size: letter portrait;
                margin: 0mm;
            }
            body {
                background-color: #ffffff;
                color: #000000;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                padding: 15mm !important;
                height: 90vh;
            }
        }
    </style>
</head>
<body class="bg-slate-100 antialiased p-4 fuente-reporte">

    <div class="max-w-3xl mx-auto mb-4 flex justify-end no-print">
        <button onclick="window.print()" class="bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition shadow-md">
            <i class="fas fa-print mr-2"></i> Imprimir / Guardar PDF
        </button>
    </div>

    <div class="max-w-3xl mx-auto bg-white p-6 border border-slate-300 shadow-lg rounded-none print-border min-h-[10.5in] flex flex-col justify-between">
        
        <div>
            <div class="text-center space-y-1 border-b-2 border-black pb-3">
                <h4 class="text-xs font-bold uppercase tracking-wide">Ministerio del Poder Popular para la Salud</h4>
                <h2 class="text-base font-bold uppercase tracking-widest">HOJA DE PEDIDO Y DESPACHO DE INSUMOS</h2>
                <p class="text-xs font-bold uppercase text-slate-600">HOSPITAL "DR. TIBURCIO GARRIDO" - CHIVACOA, EDO. YARACUY</p>
            </div>

            {{-- Metadatos de Control Diario --}}
            <div class="grid grid-cols-2 border-b border-black text-xs py-3 gap-4">
                <div>
                    <p class="uppercase"><span class="font-bold">CONTRALOR DE EXISTENCIA:</span> ALMACÉN CENTRAL</p>
                    <p class="uppercase mt-1"><span class="font-bold">SERVICIO DESTINATARIO:</span> <span class="underline font-bold text-sm">{{ $paciente->servicio }}</span></p>
                </div>
                <div class="text-right">
                    <p class="uppercase"><span class="font-bold">FECHA DE INGRESO:</span> {{ \Carbon\Carbon::parse($paciente->fecha_ingreso)->format('d/m/Y') }}</p>
                    <p class="uppercase mt-1"><span class="font-bold">N° COMPROBANTE:</span> PDF-00{{ $paciente->id }}</p>
                </div>
            </div>

            {{-- Información de Identidad del Paciente --}}
            <div class="border-b border-black py-3 text-xs space-y-2">
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2 uppercase">
                        <span class="font-bold">NOMBRE Y APELLIDO DEL PACIENTE:</span>
                        <p class="text-sm font-bold border-b border-dashed border-black pt-0.5 text-slate-900">{{ $paciente->nombre_apellido }}</p>
                    </div>
                    <div class="uppercase">
                        <span class="font-bold">CÉDULA:</span>
                        <p class="text-sm font-bold border-b border-dashed border-black pt-0.5 text-slate-900">
                            {{ is_numeric($paciente->cedula) ? number_format($paciente->cedula, 0, ',', '.') : $paciente->cedula }}
                        </p>
                    </div>
                </div>
                
                <div class="grid grid-cols-4 gap-2">
                    <div class="col-span-1 uppercase">
                        <span class="font-bold">EDAD:</span>
                        <p class="text-sm font-bold border-b border-dashed border-black pt-0.5 text-slate-900">{{ $paciente->edad }} Años</p>
                    </div>
                    <div class="col-span-3 uppercase">
                        <span class="font-bold">DIAGNÓSTICO INICIAL (Dx):</span>
                        <p class="text-sm font-bold border-b border-dashed border-black pt-0.5 text-slate-900">{{ $paciente->diagnostico }}</p>
                    </div>
                </div>
            </div>

            {{-- Sección del Tratamiento o Pedido del Almacén (Compactado) --}}
            <div class="py-4 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider bg-black text-white px-2 py-0.5 inline-block">TRATAMIENTO MÉDICO / DESCRIPCIÓN DE ARTÍCULOS SOLICITADOS</h3>
                
                <div class="border border-black rounded-none min-h-[2.8in] p-3 text-xs font-bold whitespace-pre-line leading-relaxed bg-slate-50/50 text-slate-900">
                    {{ $paciente->tratamiento ? $paciente->tratamiento : 'No se especificaron requerimientos de insumos o medicamentos adicionales en la ficha de ingreso.' }}
                </div>
            </div>
        </div>

        {{-- Bloque Inferior de Firmas Reglamentarias (Ajustado y subido) --}}
        <div class="grid grid-cols-3 gap-4 pt-4 text-[10px] text-center font-bold uppercase mt-auto">
            <div class="space-y-10">
                <div class="border-b border-black mx-4"></div>
                <p>FIRMA DEL PACIENTE / FAMILIAR</p>
            </div>
            <div class="space-y-10">
                <div class="border-b border-black mx-4"></div>
                <p>JEFE DEL SERVICIO SOLICITADO<br><span class="text-[8px] text-slate-400 font-normal">Dr.(a) Responsable</span></p>
            </div>
            <div class="space-y-10">
                <div class="border-b border-black mx-4"></div>
                <p>JEFE DE ALMACÉN / DESPACHADOR</p>
            </div>
        </div>

    </div>

    {{-- Script de Automatización --}}
    <script>
        // Lanza automáticamente el cuadro de impresión/guardado al terminar de cargar la vista
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>