<?php

/**
 * Este es el controlador WS, gestiona todas las peticiones de los endpoints, o sea,
 * peticiones del tipo https://pk.bitcampus.eu/ws/XXXXX siendo XXXXX el "primer segmento" de la URL.
 */
class Ctrl_Ws extends \zfx\Controller
{
    public function _main()
    {
        // Habilitamos el método GET al API
        $this->enableGETMethod();

        // Envía cabeceras JSON
        \zfx\HttpTools::jsonHeaders();

        // Despacha el primer segmento ejecutando la función correspondiente
        $this->_autoexec();
    }

    // --------------------------------------------------------------------

    /**
     * Mostar lista de carreteras
     * @return void
     */
    public function lst()
    {
        // Serializamos la lista tal cual obtenida del modelo
        echo json_encode(TFG::getList());
    }

    // --------------------------------------------------------------------

    /**
     * Parámetro 'code': el código de la carretera (con sus guiones)
     * Parámetro 'point': el punto a localizar en formato GeoJson, ejemplo:
     * {"type":"Point","coordinates":[-48.23456,20.12345]}
     * Devuelve un objeto json con el número de pk, ejemplo:
     * {"pk":"334,45"}
     */
    public function p2pk()
    {
        // Comprobación de los parámetros
        $cod = $this->checkPost('code');
        $this->checkPost('point', TRUE);

        // Serialización directa del resultado del modelo p2pk().
        echo json_encode(TFG::p2pk($cod, $_POST['point']));
    }

    // --------------------------------------------------------------------

    /**
     * Parámetro 'code': el código de carretera (con sus guiones)
     * Parámetro 'pk': un punto kilométrico (con coma o punto decimal)
     * Devuelve un array de objetos objeto con el id de la vía, el código y geojson correspondiente al punto kilométrico especificado de la carretera.
     * Ejemplo:
     * [
     *      {
     *          "id":"115",
     *          "code":"AP-7",
     *          "point":{"type":"Point","crs":{"type":"name","properties":{"name":"EPSG:25830"}},"coordinates":[336728.51364334,4042652.182931964]}
     *      },
     *      {
     *          "id":"330",
     *          "code":"AP-7",
     *          "point":{"type":"Point","crs":{"type":"name","properties":{"name":"EPSG:25830"}},"coordinates":[895917.791743337,4591124.361498231]}
     *      }
     * ]
     */
    public function pk2p()
    {
        // Comprobación de los parámetros
        $cod = $this->checkPost('code');
        $pk  = $this->checkPost('pk');

        // Llamamos al modelo, función pk2p.
        $r = TFG::pk2p($cod, new \zfx\Num($pk));

        // Si es NULL, hemos terminado (no se encontró un punto viable para el PK especificado).
        if (!$r) {
            echo json_encode(NULL);
        }

        // Si tenemos resultados, vamos a procesarlos manualmente para evitar una doble serialización,
        // ya que PostGIS nos ofrece un dato en formato GeoJSON, pero no todos.
        // Como esta capa está a cargo de la serialización, es mucho mejor realizarlo aquí.
        // Aunque sería más fácil desde PostgreSQL, es preferible preservar la organización y
        // responsabilidades del código.
        else {
            $sal = [];
            foreach ($r as $k => $v) {
                $o        = new \stdClass();
                $o->id    = $v['id'];
                $o->code  = $v['codigo'];
                $o->point = (int)$v['id'];
                $sal[]    = $o;
            }
            $jsonCode = json_encode($sal);
            foreach ($r as $k => $v) {
                $jsonCode = str_replace('"point":' . $v['id'], '"point":' . $v['punto'], $jsonCode);
            }

            // Devolvemos al cliente el JSON correcto.
            echo $jsonCode;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Parámetro 'code': el código de la carretera (con sus guiones)
     * Parámetro 'point': el punto a localizar en formato GeoJson, ejemplo:
     * {"type":"Point","coordinates":[-48.23456,20.12345]}
     * Devuelve un objeto json con la via y el número de pk, ejemplo:
     * {"code":"N-322","pk":"365.383"}
     */
    public function p2viapk()
    {
        // Chequeo de parámetro
        $this->checkPost('point', TRUE);

        // Serialización directa de los resultados de p2viapk.
        echo json_encode(TFG::p2viapk($_POST['point']));
    }


    // --------------------------------------------------------------------

    /**
     * Función interna que comprueba cierto parámetro POST, y su correcto formato JSON en caso necesario.
     * @param $var
     * @param $json
     * @return mixed|void
     */
    private function checkPost($var, $json = FALSE)
    {
        $o        = new \stdClass();
        $o->error = TRUE;
        // Comprueba si la variable con ese nombre ($var) existe en POST.
        if (!isset($_POST[$var]) || \zfx\trueEmpty($_POST[$var])) {
            $o->desc = "Se necesita especificar la variable [$var].";
            echo json_encode($o);
            die;
        }
        // Si además era JSON, se parsea para validarla y obtener los errores.
        if ($json) {
            $data = json_decode($_POST[$var]);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $o->desc = "Error JSON al interpretar la variable [$var]: " . json_last_error_msg();
                echo json_encode($o);
                die;
            }
        }
        // Si todo fue bien, se devuelve el valor por conveniencia.
        else {
            $data = $_POST[$var];
        }
        return $data;
    }

    // --------------------------------------------------------------------

    /**
     * Esta función mapea las variables aceptadas por el API obtenidas por GET a POST
     * ejerciendo de capa de compatibilidad con el método GET.
     * @return void
     */
    private function enableGETMethod()
    {
        // Mapeo de posibles variables obtenidas por el método GET
        foreach (['code', 'point', 'pk'] as $variable) {
            if (isset($_GET[$variable])) {
                $_POST[$variable] = $_GET[$variable];
            }
        }
    }

    // --------------------------------------------------------------------

}
