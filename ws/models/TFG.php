<?php

class TFG
{
    /**
     * Obtener la lista de vías disponibles
     * @return array|false|mixed|null
     */
    public static function getList()
    {
        $db  = new \zfx\DB();
        $sql =
            "
                select id, codigo, denominacion
                from via
                order by codigo;
            ";
        return $db->qa($sql, 'id');
    }

    // --------------------------------------------------------------------

    /**
     * Invoca a la función p2pk() pasándole el código de vía y el punto en formato geojson
     * @param $codigo
     * @param $punto
     * @return array|false|mixed|null
     */
    public static function p2pk($codigo, $punto)
    {
        // Otra forma clásica es usar consultas almacenadas, pero el proceso aquí realizado es totalmente equivalente
        // y permite adaptación a subtipos de datos adicionales.
        $db     = new \zfx\DB();
        $codigo = $db->escape(\zfx\StrFilter::upperCase($codigo));
        $punto  = $db->escape($punto);
        $sql    = "select p2pk('$codigo', st_transform(st_geomfromgeojson('$punto'), 25830))";
        return $db->qr($sql);
    }

    // --------------------------------------------------------------------

    /**
     * Invoca a la función p2viapk() pasándole el punto en formato geojson
     * @param $punto
     * @return array|false|mixed|null
     */
    public static function p2viapk($punto)
    {
        $db    = new \zfx\DB();
        $punto = $db->escape($punto);
        $sql   = "select codigo as code, pk from p2viapk(st_transform(st_geomfromgeojson('$punto'), 25830))";
        $res = $db->qr($sql);
        return $res;
    }

    // --------------------------------------------------------------------

    /**
     * Invoca a la función pk2p() pasando el código de la vía y el número de pk)
     * @param $codigo
     * @param \zfx\Num $pk
     * @return array|false|mixed|null
     */
    public static function pk2p($codigo, \zfx\Num $pk)
    {
        $db    = new \zfx\DB();
        $codigo = $db->escape(\zfx\StrFilter::upperCase($codigo));
        $pknum = $pk->format(3, '.', '');
        $sql   = "select r.via_id as id, r.codcarr as codigo, st_asgeojson(r.geom) as punto from pk2p('$codigo', $pknum) r";
        $res = $db->qa($sql);
        return $res;
    }

    // --------------------------------------------------------------------

}
