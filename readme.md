# Determinación precisa del punto kilométrico de una vı́a correspondiente a unas coordenadas geográficas
## Repositorio de código del TFG
- Universidad: UOC - Universitat Oberta de Catalunya
- Área: Servicios basados en localización y espacio inteligente
- Autor: Jorge A. Montes Pérez

# Directorio `sql/`
Contiene el código SQL y PL/pgSQL del proyecto.

- Código SQL para la creación del modelo de datos: `schema.sql`
- Scripts de importación y adaptación de datasets RCE: `importar-rce.sql`
- Scripts de importación y adaptación de datasets BTN: `importar-btn.sql`
- Procesos sobre BTN: `procesos-btn.sql`
- Código de las funciones PL/pgSQL: `funciones.sql`
- Obtención de métricas: `metricas.sql`
- Pruebas interactivas: `pruebas.sql`
- Prueba de rendimiento: `rendimiento.sql`
- Validación con datasets reales: `validacion-apps.sql`
- Validación con PK calibrados: `validacion-cal.sql`

# Directorio `ws/`
Contiene el código PHP del servicio web.

- Controlador WS, gestiona todas las peticiones de endpoint: `controllers/Ctrl_Ws.php`
- Modelo TFG, fachada envoltorio a las funciones PL/pgSQL: `models/TFG.php`

### Infraestructura de soporte

El resto de ficheros en el directorio `ws/` constituyen el framework ligero PHP usado para construir el servicio web
y **no forman parte del contenido evaluable**.
- Clases del framework: `base/`
- Configuración: `cfg/`
- Controlador index: `controllers/Ctrl_Index.php`
- Vistas (HTML, etc.) `views/`
- Controlador frontal: `index.php`
- Directivas Apache: `.htaccess`

# Directorio `misc/`
Contiene los datasets utilizados en el proyecto:

- Catálogo Red de Carreteras del Estado: `rce/`. Se incluyen originales y exportaciones a SQL.
- Base de datos Topográfica Nacional: `btn/`. Solo exportaciones a SQL por motivos de espacio. Los originales se pueden conseguir en: https://centrodedescargas.cnig.es/CentroDescargas/btn