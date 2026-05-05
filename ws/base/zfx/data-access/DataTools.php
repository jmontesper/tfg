<?php
/*
  Zerfrex (R) Web Framework (ZWF)

  Copyright (c) 2012-2022 Jorge A. Montes Pérez <jorge@zerfrex.com>
  All rights reserved.

  Redistribution and use in source and binary forms, with or without
  modification, are permitted provided that the following conditions
  are met:
  1. Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
  2. Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in the
  documentation and/or other materials provided with the distribution.
  3. Neither the name of copyright holders nor the names of its
  contributors may be used to endorse or promote products derived
  from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
  ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
  TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
  PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
  BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
  SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
  INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
  CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
  POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @package data-access
 */

namespace zfx;

use DateTime;

class DataTools
{

    /**
     * Convierte y transforma un array de valores enteros a una cadena "n1,n2,n3..."
     * para ser usada dentro de una cláusula IN (...) por ejemplo.
     *
     * @param array $data
     * @return string
     */
    public static function intList(array $data = NULL)
    {
        if (!$data) {
            return '';
        }
        end($data);
        $last = key($data);
        $res  = '';
        foreach ($data as $k => $v) {
            $res .= (int)$v;
            if ($k != $last) {
                $res .= ',';
            }
        }
        return $res;
    }
    // --------------------------------------------------------------------

    /**
     * Casts and tranforms an array of string values to a 'n1','n2','n3'... string.
     * @param array $data
     * @return string
     */
    public static function strList(array $data = NULL)
    {
        if (!$data) {
            return '';
        }
        end($data);
        $last = key($data);
        $res  = '';
        foreach ($data as $k => $v) {
            $res .= "'" . DB::escape($v) . "'";
            if ($k != $last) {
                $res .= ',';
            }
        }
        return $res;
    }

    // --------------------------------------------------------------------

    /**
     * Construye un objeto DateTime a partir de una cadena de fecha obtenida de la BD
     *
     * @param $dateString
     * @return DateTime|null
     */
    public static function getDate($dateString)
    {
        $ret = DateTime::createFromFormat(Config::get('dbDateFormat'), (string)$dateString);
        if (!$ret instanceof DateTime) {
            $ret = NULL;
        }
        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Construye un objeto DateTime a partir de una cadena de hora obtenida de la BD
     *
     * @param $dateString
     * @return DateTime|false|null
     */
    public static function getTime($dateString)
    {
        $ret = DateTime::createFromFormat(Config::get('dbTimeFormat'), $dateString);
        if (!$ret instanceof DateTime) {
            $ret = NULL;
        }
        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Construye un objeto DateTime a partir de una cadena de fecha y hora obtenida de la BD
     *
     * @param $dateString
     * @return DateTime|false|null
     */
    public static function getDateTime($dateString)
    {
        $ret = DateTime::createFromFormat(Config::get('dbDateTimeFormat'), $dateString);
        if (!$ret instanceof DateTime) {
            $ret = NULL;
        }
        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * Convertir un valor DateTime a un valor SQL DATE.
     * Por defecto le añade las comillas, pero se puede desactivar.
     * Si el valor datetime es null se devuelve un NULL, pero si se desactivan las comillas
     * entonces se devuelve una cadena vacía.
     * @param DateTime|null $dtValue
     * @param bool $quote
     * @return string
     */
    public static function dbDate(\DateTime $dtValue = NULL, $quote = TRUE)
    {
        if (!$dtValue) {
            if ($quote) {
                return 'null';
            }
            else {
                return '';
            }
        }
        $str = '';
        if ($quote) {
            $str .= "'";
        }
        $str .= $dtValue->format(Config::get('dbDateFormat'));
        if ($quote) {
            $str .= "'";
        }
        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Convertir un valor DateTime a un valor SQL TIME.
     * Por defecto le añade las comillas, pero se puede desactivar.
     * Si el valor datetime es null se devuelve un NULL, pero si se desactivan las comillas
     * entonces se devuelve una cadena vacía.
     * @param DateTime|null $dtValue
     * @param bool $quote
     * @return string
     */
    public static function dbTime(\DateTime $dtValue = NULL, $quote = TRUE)
    {
        if (!$dtValue) {
            if ($quote) {
                return 'null';
            }
            else {
                return '';
            }
        }
        $str = '';
        if ($quote) {
            $str .= "'";
        }
        $str .= $dtValue->format(Config::get('dbTimeFormat'));
        if ($quote) {
            $str .= "'";
        }
        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Convertir un valor DateTime a un valor SQL TIMESTAMP / DATETIME
     * Por defecto le añade las comillas, pero se puede desactivar.
     * Si el valor datetime es null se devuelve un NULL, pero si se desactivan las comillas
     * entonces se devuelve una cadena vacía.
     * @param DateTime|null $dtValue
     * @param bool $quote
     * @return string
     */
    public static function dbDateTime(\DateTime $dtValue = NULL, $quote = TRUE)
    {
        if (!$dtValue) {
            if ($quote) {
                return 'null';
            }
            else {
                return '';
            }
        }
        $str = '';
        if ($quote) {
            $str .= "'";
        }
        $str .= $dtValue->format(Config::get('dbDateTimeFormat'));
        if ($quote) {
            $str .= "'";
        }
        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Función ultrarrápida para obtener un registro dado su ID numérico
     * la PK debe llamarse 'id'.
     * @param $tableName
     * @param $id
     * @param string $profile
     * @poaram string $col
     * @return array|mixed|void|null
     */
    public static function getById($tableName, $id, $profile = '', $select = '*', $col = NULL)
    {
        $db  = new \zfx\DB($profile);
        $id  = (int)$id;
        $sql =
            "
                SELECT      $select
                FROM        {$db::quote($tableName)}
                WHERE       id = $id;
            ";
        return $db->qr($sql, $col);
    }

    // --------------------------------------------------------------------

    /**
     * Función ultrarrápida para obtener una lista completa usando la columna 'id' como PK
     *
     * @param string $tableName
     * @param string $column
     * @param string $where SIN LA CLAUSULA 'where'
     * @param string $profile
     * @param string $keycol Usar esta columna como clave primaria.
     * @param string $columnList Ignorar $column y usar esta lista de columnas.
     * @return array|mixed|void|null
     */
    public static function getList($tableName, $column = '', $where = '', $profile = '', $keycol = 'id', $columnList = '')
    {
        $db = new \zfx\DB($profile);
        if (!trueEmpty($columnList)) {
            $select = $columnList;
        }
        elseif (!trueEmpty($column)) {
            $select = $db::quote($keycol) . ', ' . $db::quote($column);
        }
        else {
            $select = '*';
        }
        if (!trueEmpty($where)) {
            $where = "WHERE " . $where;
        }
        $sql =
            "
                SELECT      $select
                FROM        {$db::quote($tableName)}
                $where
                ORDER BY    {$db::quote($keycol)};
            ";
        return $db->qa($sql, $keycol, $column);
    }

    // --------------------------------------------------------------------

    public static function dbBoolean($value, $quote = FALSE): string
    {
        if (is_null($value)) {
            if ($quote) {
                return 'null';
            }
            else {
                return '';
            }
        }

        $ivalue = (int)$value;
        $svalue = \zfx\StrFilter::upperCase((string)$value);

        $str = 'false';
        if ($value === TRUE || $ivalue > 0 || $svalue === 'T' || $svalue === 'TRUE' || $svalue === 'V' || $svalue === 'VERDADERO') {
            $str = 'true';
        }
        if ($quote) {
            return "'" . $str . "'";
        }
        else {
            return $str;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get Record: Devuelve una única fila, bien completa o bien solo una columna
     *
     * Esta función detecta automáticamente el tipo de datos de la columna por el prefijo n,s dando por hecho que una clave
     * es int o string.
     *
     * @param string $table La tabla a consultar
     * @param string $keyCol Usar esta columna como clave
     * @param mixed $value El ID de la fila
     * @param string $colValue Opcional, si se especifica, devolver únicamente el valor de dicha columna
     * @param string $finale Añadir esto al final de la SQL (por ejemplo para hacer un ORDER BY, LIMIT, etc).
     * @param string $select Si $colValue es null, se hace un select * pero se puede especificar aquí otra cosa en lugar del asterisco
     * @param string $perfil El perfil de la BD a usar
     * @return mixed
     */
    public static function getR($table, $keyCol, $value, $colValue = NULL, $finale = '', $select = '*', $perfil = NULL)
    {
        $db     = new DB($perfil);
        $prefix = substr($keyCol, 0, 1);
        $table  = $db->quote($table);
        $keyCol = $db->quote($keyCol);
        if ($prefix == 'n') {
            $value = (int)$value;
        }
        else {
            $value = "'" . $db->escape($value) . "'";
        }

        if ($colValue == NULL) {
            $sql = "SELECT $select FROM $table WHERE $keyCol = $value $finale";
        }
        else {
            $column = $db->quote($colValue);
            $sql    = "SELECT $column FROM $table WHERE $keyCol = $value $finale";
        }
        return $db->qr($sql, $colValue);
    }

    // --------------------------------------------------------------------

    /**
     * Get Record: Devuelve una única fila, bien completa o bien solo una columna, usando LIKE
     *
     * Esta función da por hecho que la clave es string.
     *
     * @param string $table La tabla a consultar
     * @param string $keyCol Usar esta columna como clave
     * @param mixed $value El ID de la fila, se hará un LIKE y no se escapará
     * @param string $colValue Opcional, si se especifica, devolver únicamente el valor de dicha columna
     * @param string $finale Añadir esto al final de la SQL (por ejemplo para hacer un ORDER BY, LIMIT, etc).
     * @param string $select Si $colValue es null, se hace un select * pero se puede especificar aquí otra cosa en lugar del asterisco
     * @param string $perfil El perfil de la BD a usar
     * @return mixed
     */
    public static function getRL($table, $keyCol, $value, $colValue = NULL, $finale = '', $select = '*', $perfil = NULL)
    {
        $db     = new DB($perfil);
        $table  = $db->quote($table);
        $keyCol = $db->quote($keyCol);

        if ($colValue == NULL) {
            $sql = "SELECT $select FROM $table WHERE $keyCol LIKE '$value' $finale";
        }
        else {
            $column = $db->quote($colValue);
            $sql    = "SELECT $column FROM $table WHERE $keyCol LIKE '$value' $finale";
        }
        return $db->qr($sql, $colValue);
    }


    // --------------------------------------------------------------------

    /**
     * Get RecordSet: Devuelve un conjunto de filas, bien completas, o bien solo una columna
     *
     * Esta función detecta automáticamente el tipo de datos de la columna por el prefijo n,s dando por hecho que una clave
     * primaria es int o string.
     *
     * @param string $table La tabla a consultar
     * @param string $keyCol Usar esta columna como clave primaria
     * @param mixed $value La clave, o si es un array conjunto de claves, o vacío para recibir todas las filas
     * @param string $colKey Opcional, si se especifica, usar el valor de esta columna como clave en el array que se devolverá
     * @param string $colValue Opcional, si se especifica, devolver únicamente el valor de dicha columna
     * @param string $finale Añadir esto al final de la SQL (por ejemplo para hacer un ORDER BY, LIMIT, etc).
     * @param string $select Si $colValue es null, se hace un select * pero se puede especificar aquí otra cosa en lugar del asterisco
     * @param string $perfil El perfil de la BD a usar
     * @return mixed
     */
    public static function getRS($table, $keyCol, $value = '', $colKey = '', $colValue = '', $finale = '', $select = '*', $perfil = NULL)
    {
        $db     = new DB($perfil);
        $prefix = substr($keyCol, 0, 1);
        $table  = $db->quote($table);
        $keyCol = $db->quote($keyCol);
        if ($prefix == 'n') {
            if (is_array($value)) {
                $intList = self::intList($value);
                if ($intList == '') {
                    $where = 'FALSE';
                }
                else {
                    $where = "$keyCol IN (" . $intList . ")";
                }
            }
            else {
                if ($value == '') {
                    $where = 'TRUE';
                }
                else {
                    $where = "$keyCol = " . (int)$value;
                }
            }
        }
        else {
            if (is_array($value)) {
                $strList = self::strList($value);
                if ($strList == '') {
                    $where = 'FALSE';
                }
                else {
                    $where = "$keyCol IN (" . $strList . ")";
                }
            }
            else {
                if ($value == '') {
                    $where = 'TRUE';
                }
                else {
                    $where = "$keyCol = '" . $db->escape($value) . "'";
                }
            }
        }
        if ($colValue == NULL || $colKey != '') {
            $sql = "SELECT $select FROM $table WHERE $where $finale";
        }
        else {
            $column = $db->quote($colValue);
            $sql    = "SELECT $column FROM $table WHERE $where $finale";
        }
        return $db->qa($sql, $colKey, $colValue);
    }

    // --------------------------------------------------------------------

    /**
     * Get RecordSet: Devuelve un conjunto de filas, bien completas, o bien solo una columna, usando LIKE
     *
     * Esta función da por hecho que la clave es string.
     *
     * @param string $table La tabla a consultar
     * @param string $keyCol Usar esta columna como clave primaria
     * @param mixed $value La clave, que admite sintaxis LIKE como por ejemplo %.
     * @param string $colKey Opcional, si se especifica, usar el valor de esta columna como clave en el array que se devolverá
     * @param string $colValue Opcional, si se especifica, devolver únicamente el valor de dicha columna
     * @param string $finale Añadir esto al final de la SQL (por ejemplo para hacer un ORDER BY, LIMIT, etc).
     * @param string $select Si $colValue es null, se hace un select * pero se puede especificar aquí otra cosa en lugar del asterisco
     * @param string $perfil El perfil de la BD a usar
     * @return mixed
     */
    public static function getRSL($table, $keyCol, $value, $colKey = '', $colValue = '', $finale = '', $select = '*', $perfil = NULL)
    {
        $db     = new DB($perfil);
        $table  = $db->quote($table);
        $keyCol = $db->quote($keyCol);
        $where  = "$keyCol LIKE '$value'";
        if ($colValue == NULL || $colKey != '') {
            $sql = "SELECT $select FROM $table WHERE $where $finale";
        }
        else {
            $column = $db->quote($colValue);
            $sql    = "SELECT $column FROM $table WHERE $where $finale";
        }
        return $db->qa($sql, $colKey, $colValue);
    }

    // --------------------------------------------------------------------

}
