<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicio Web de Referenciación Lineal (LRS)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #0f172a;
            color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            padding: 2rem;
        }
        h1 {
            color: #38bdf8;
            margin-bottom: 0.5rem;
        }
        h2 {
            color: #94a3b8;
            font-weight: normal;
            margin-top: 0;
        }
        p {
            line-height: 1.6;
        }
        code {
            background: #1e293b;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            color: #facc15;
        }
        .box {
            background: #111827;
            border: 1px solid #1f2937;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        .footer {
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Servicio Web de Referenciación Lineal</h1>
    <h2>(Linear Referencing System - LRS)</h2>

    <p>
        Bienvenido. Este endpoint forma parte de un <strong>servicio web</strong>, no de un sitio web tradicional.
        No está diseñado para navegación humana ni contiene interfaz gráfica interactiva.
    </p>

    <p>
        Su propósito es proporcionar funcionalidades de <strong>referenciación lineal sobre infraestructuras</strong>,
        permitiendo transformar entre coordenadas geográficas y puntos kilométricos (PK).
    </p>

    <div class="box">
        <p><strong>Funciones principales:</strong></p>
        <ul>
            <li>Obtención del PK a partir de coordenadas (<code>p → PK</code>)</li>
            <li>Obtención de coordenadas a partir de PK (<code>PK → p</code>)</li>
            <li>Cálculo del punto más cercano sobre una vía</li>
            <li>Localización de la infraestructura lineal más próxima</li>
        </ul>
    </div>

    <div class="box">
        <p><strong>Uso:</strong></p>
        <p>
            Este servicio está pensado para ser consumido mediante peticiones HTTP (REST),
            devolviendo resultados estructurados en formatos como <code>JSON</code> o <code>GeoJSON</code>.
        </p>
    </div>

    <div class="footer">
        <p>Trabajo de Fin de Grado (Ingeniería Informática UOC). Autor: Jorge A. Montes Pérez.</p>
        <p>Esta página tiene carácter meramente informativo y no forma parte del desarrollo evaluable del TFG. Ha sido generada con IA.</p>
    </div>
</div>
</body>
</html>