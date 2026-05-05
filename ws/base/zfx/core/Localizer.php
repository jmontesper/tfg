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

namespace zfx;

use DateTime;

/**
 * Localizer
 */
class Localizer
{
    // Opciones (campo de bits) para LocalizeRecord
    /**
     * Los campos designados como fechas son expandidos para incluir
     * por separado el dia _dd, mes _mm, año _yyyy, dia semana (1...7) _ww,
     * así como el nombre del mes y de la semana localizados _mn y _wn
     */
    const OPT_EXPAND_DATES = 1;


    /**
     * Language code
     * @var string $lang
     */
    private $lang;

    /**
     * Language info array
     * @var array
     */
    private $langInfo;

    /**
     * Currently loaded i18n string sections
     * @var array
     */
    private $i18nSections;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $lang Use this language instead of default system language.
     */
    public function __construct($lang = NULL)
    {
        if (trueEmpty($lang)) {
            $this->setLang($lang);
        }
        else {
            $this->lang     = $lang;
            $this->langInfo = a(Config::get('languageInfo'), $this->lang);
        }
        $this->i18nSections = array();
    }

    // --------------------------------------------------------------------

    /**
     * Get localized representation of a boolean value.
     *
     * @param boolean $value
     * @return string
     */
    public function getBoolean($value)
    {
        if ($value) {
            return $this->langInfo['true'];
        }
        else {
            return $this->langInfo['false'];
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get localized representation of a NULL value.
     *
     * @return string
     */
    public function getNull()
    {
        return $this->langInfo['null'];
    }

    // --------------------------------------------------------------------

    /**
     * Get localized date of a \DateTime value
     *
     * @param DateTime $value
     * @return string
     */
    public function getDate(DateTime $value = NULL)
    {
        if (!$value instanceof DateTime) {
            return NULL;
        }
        else {
            return $value->format($this->langInfo['date']);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get localized time of a \DateTime value
     *
     * @param DateTime $value
     * @return string
     */
    public function getTime(DateTime $value = NULL)
    {
        if (!$value instanceof DateTime) {
            return NULL;
        }
        else {
            return $value->format($this->langInfo['time']);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get localized date and time of a \DateTime value
     * @param DateTime $value
     * @return string
     */
    public function getDateTime(DateTime $value = NULL)
    {
        if (!$value instanceof DateTime) {
            return NULL;
        }
        else {
            return $value->format($this->langInfo['dateTime']);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get localized representation of a floating-point value.
     *
     * @param float $value Value to convert
     * @param integer $precision Number of precision digits
     * @param boolean $sep If TRUE, print thousand separator
     * @return string Representation
     */
    public function getFloat($value, $precision = 5, $sep = FALSE)
    {
        $value = round((float)$value, $precision);
        if ($sep) {
            return number_format($value, $precision, $this->langInfo['dec'], $this->langInfo['sep']);
        }
        else {
            return number_format($value, $precision, $this->langInfo['dec'], '');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get localized representation of a \zfx\Num instance.
     *
     * @param float $value Value to convert
     * @param integer $precision Number of precision digits
     * @param boolean $sep If TRUE, print thousand separator
     * @return string Representation
     */
    public function getNum(\zfx\Num $value, $precision = 6, $sep = FALSE)
    {
        if ($sep) {
            return $value->format($precision, $this->langInfo['dec'], $this->langInfo['sep']);
        }
        else {
            return $value->format($precision, $this->langInfo['dec'], '');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get localized representation of a integer
     *
     * @param float $value Value to convert
     * @param boolean $sep If TRUE, print thousand separator
     * @return string Representation
     */
    public function getInteger($value, $sep = FALSE)
    {
        if ($sep) {
            return number_format($value, 0, $this->langInfo['dec'], $this->langInfo['sep']);
        }
        else {
            return number_format($value, 0, $this->langInfo['dec'], '');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get current language set
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    // --------------------------------------------------------------------

    /**
     * Set current language
     *
     * @param string $lang
     */
    public function setLang($lang)
    {
        if (trueEmpty($lang) || !in_array($lang, Config::get('languages'))) {
            $this->lang = Config::get('defaultLanguage');
        }
        else {
            $this->lang = $lang;
        }
        $this->langInfo = a(Config::get('languageInfo'), $this->lang);
    }

    // --------------------------------------------------------------------

    /**
     * Get locale language info
     *
     * @return array
     */
    public function getLangInfo()
    {
        return $this->langInfo;
    }

    // --------------------------------------------------------------------

    /**
     * Set locale language info
     *
     * @param array $value
     */
    public function setLangInfo(array $value)
    {
        $this->langInfo = $value;
    }

    // --------------------------------------------------------------------

    /**
     * Establecer información de idioma local a una clave dada
     *
     * @param string $key Clave a modificar
     * @param string $value Valor a establecer
     */
    public function setLangInfoKey($key, $value)
    {
        $this->langInfo[$key] = $value;
    }

    // --------------------------------------------------------------------

    /**
     * Convert a localized datetime value to a DateTime object
     *
     * @param string $value Localized datetime
     * @return DateTime|NULL if error
     */
    public function interpretDateTime($value, $allowISO = FALSE)
    {
        $dt = FALSE;
        if ($allowISO) {
            $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value);
            if (!$dt) {
                $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $value);
            }
            if (!$dt) {
                $dt = DateTime::createFromFormat('Y-m-d\TH:i', $value);
            }
        }
        if ($dt === FALSE) {
            $dt = DateTime::createFromFormat($this->langInfo['dateTime'], $value);
            $d  = DateTime::createFromFormat($this->langInfo['date'], $value);
        }
        if ($dt === FALSE) {
            if ($d === FALSE) {
                return NULL;
            }
            else {
                $d->setTime(0, 0, 0);
                return $d;
            }
        }
        else {
            return $dt;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Convert a localized time value to a DateTime object
     *
     * @param string $value Localized time
     * @return DateTime|NULL if error
     */
    public function interpretTime($value, $allowISO = FALSE)
    {
        // Si permitimos ISO, tendrá prioridad frente a la l10n
        $dt = FALSE;
        if ($allowISO) {
            $dt = DateTime::createFromFormat('H:i:s', $value);
            if ($dt === FALSE) {
                $dt = DateTime::createFromFormat('H:i', $value);
            }
        }
        if ($dt === FALSE) {
            $dt = DateTime::createFromFormat($this->langInfo['time'], $value);
        }
        if ($dt === FALSE) {
            $dt = NULL;
        }
        return $dt;
    }

    // --------------------------------------------------------------------

    /**
     * Convert a localized date value to a DateTime object
     *
     * @param string $value Localized date
     * @return DateTime|NULL if error
     */
    public function interpretDate($value, $allowISO = FALSE)
    {
        // Si permitimos ISO, tendrá prioridad frente a la l10n
        $dt = FALSE;
        if ($allowISO && StrValidator::dateISO($value)) {
            $dt = DateTime::createFromFormat('Y-m-d', $value);
        }
        if ($dt === FALSE) {
            $dt = DateTime::createFromFormat($this->langInfo['date'], $value);
        }
        if ($dt === FALSE) {
            $dt = NULL;
        }
        return $dt;
    }

    // --------------------------------------------------------------------

    public function getString($section, $key, $fallBackValue = '')
    {
        if (!av($this->i18nSections, $section) || !Config::get('i18n_cache')) {
            $path = Config::get('cfgPath') . $this->lang . DIRECTORY_SEPARATOR . $section . '.php';
            if (file_exists($path)) {
                include($path);
                if (isset($i18n)) {
                    $this->i18nSections[$section] = $i18n;
                }
            }
        }
        $ret = aa($this->i18nSections, $section, $key);
        if (trueEmpty($ret)) {
            return $fallBackValue;
        }
        else {
            return $ret;
        }
    }

    // --------------------------------------------------------------------

    public function localizeRecordSet(array $specs, array &$recordSet = NULL, $options = 0)
    {
        if (!$recordSet) {
            return;
        }
        foreach ($recordSet as &$record) {
            $this->localizeRecord($specs, $record, $options);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Dado un registro (array) tal y como sale de la base de datos,
     * localizamos en el idioma actual, o transformamos cada valor,
     * según las especificaciones ($specs).
     *
     * Las especificaciones consisten en un array (mapa) cuya clave es el nombre
     * del campo y el valor es el formato. Los formatos son los siguientes:
     *
     * 'date'                        El campo se transformará en una fecha bien formada
     * 'time'                        Una hora
     * 'dateTime'                    Fecha y hora
     * 'boolean'                     Se interpreta como un boolean, se imprime sí/no
     * 'num|n'                       Un número con n decimales
     * 'checkbox|str'                Se interpreta como un boolean, se imprime la cadena str con una caja marcada o no (unicode)
     * 'callback|str'                Se llama a la función str con el valor pasado como parámetro, el valor definitivo será la salida de dicha función
     * 'pref|str'                    Usa str como prefijo del valor
     * 'suf|str'                     Usa str como sufijo del valor
     * array                         Si es un array, se trata como un stringmap.
     * 'fieldImagePath|tabla|campo'  Ruta local de la imagen correspondiente a ese campo
     * 'fieldFilePath|tabla|campo'   Ruta local del fichero correspondiente a ese campo
     * 'fieldImageUrl|tabla|campo'   URL de la imagen correspondiente a ese campo
     * 'fieldFileUrl|tabla|campo'    URL del fichero correspondiente a ese campo
     * 's2t'                         Son segundos y debe mostrarse como HHH:MM
     *
     * @param array $specs Array con las especificaciones
     * @param array|null $record RecordSet a tratar
     * @param int $options Campo de bits de opciones.
     */
    public function localizeRecord(array $specs, array &$record = NULL, $options = 0)
    {
        if (!$record) {
            return;
        }
        $addenda = [];
        foreach ($record as $fieldKey => &$fieldValue) {
            if (!array_key_exists($fieldKey, $specs)) {
                continue;
            }
            // ¿Es un mapa?
            if (is_array($specs[$fieldKey])) {
                $fieldValue = aa($specs, $fieldKey, $fieldValue);
            }
            else // Lo formateamos según el tipo de dato
            {
                $params = explode('|', $specs[$fieldKey]);
                switch ($params[0]) {
                    case 'date':
                    {
                        $dt         = DataTools::getDate($fieldValue);
                        $fieldValue = $this->getDate($dt);
                        if (($options & self::OPT_EXPAND_DATES) && $dt) {
                            $addenda[$fieldKey . '_dd']   = (int)$dt->format('d');
                            $addenda[$fieldKey . '_mm']   = (int)$dt->format('m');
                            $addenda[$fieldKey . '_yyyy'] = (int)$dt->format('Y');
                            $addenda[$fieldKey . '_ww']   = (int)$dt->format('w') + 1;
                            $addenda[$fieldKey . '_mn']   = \CalTools::monthName((int)$dt->format('m'), $this->getLang());
                            $addenda[$fieldKey . '_wn']   = \CalTools::dayName((int)$dt->format('w') + 1, $this->getLang());
                        }
                        break;
                    }
                    case 'dateTime':
                    {
                        $dt         = DataTools::getDateTime($fieldValue);
                        $fieldValue = $this->getDateTime($dt);
                        break;
                    }
                    case 'time':
                    {
                        $dt         = DataTools::getTime($fieldValue);
                        $fieldValue = $this->getTime($dt);
                        break;
                    }
                    case 'boolean':
                    {
                        $value      = ($fieldValue == 't' || $fieldValue == '1');
                        $fieldValue = $this->getBoolean($value);
                        break;
                    }
                    case 'num':
                    {
                        $num        = new Num($fieldValue);
                        $fieldValue = $this->getNum($num, (int)$params[1]);
                        break;
                    }
                    case 'checkbox':
                    {
                        if ($fieldValue == 't' || $fieldValue == '1') {
                            $fieldValue = '☑' . ' ' . $params[1];
                        }
                        else {
                            $fieldValue = '☐' . ' ' . $params[1];
                        }
                        break;
                    }
                    case 'callback':
                    {
                        $fieldValue = call_user_func($params[1], $fieldValue);
                        break;
                    }
                    case 'pref':
                    {
                        $fieldValue = $params[1] . $fieldValue;
                        break;
                    }
                    case 'suf':
                    {
                        $fieldValue = $fieldValue . $params[1];
                        break;
                    }
                    case 'fieldImagePath':
                    {
                        $fieldValue = FieldViewImage::getLocalFile($params[1], $params[2], $fieldValue);
                        break;
                    }
                    case 'fieldFilePath':
                    {
                        $fieldValue = FieldViewFile::getLocalFile($params[1], $params[2], $fieldValue);
                        break;
                    }
                    case 'fieldImageUrl':
                    {
                        $fieldValue = FieldViewImage::getLocalUrl($params[1], $params[2], $fieldValue);
                        break;
                    }
                    case 'fieldFileUrl':
                    {
                        $fieldValue = FieldViewFile::getLocalUrl($params[1], $params[2], $fieldValue);
                        break;
                    }
                    case 's2t':
                    {
                        $ts         = (int)$fieldValue;
                        $h          = (int)($ts / 3600);
                        $m          = (int)(($ts % 3600) / 60);
                        $fieldValue = sprintf("%02u:%02u", $h, $m);
                        break;
                    }
                }
            }
        }
        // Añadimos los adicionales, si los hubiera
        if ($addenda) {
            foreach ($addenda as $k => $v) {
                $record[$k] = $v;
            }
        }
    }

    // --------------------------------------------------------------------

}
