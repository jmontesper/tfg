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
 * Core Framework functions
 *
 * @package core
 */

namespace zfx;

use Countable;

/**
 * Array access wrapper
 *
 * Returns the value of specified $key.
 * Returns $noValue (null by default) if specified $key does not exist or
 * $array is not an array.
 *
 * @param mixed $array Variable to be checked
 * @param mixed $key Key to be retrieved
 * @param mixed $noValue In case of missing $key, return this value
 * @return mixed
 */
function a($array, $key, $noValue = NULL)
{
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    }
    else {
        return $noValue;
    }
}

// --------------------------------------------------------------------

/**
 * Nested array access wrapper
 *
 * @param $array
 * @param $key1 , $key2, ... (variable parameters)
 * If $key1 is an array, it will be treated as a list of keys.
 *
 * @return The value of $array[$key1[$key2[...]]] or NULL if not found
 */
function aa()
{
    $keys  = func_get_args();
    $array = array_shift($keys);

    if (!is_array($array)) {
        return NULL;
    }

    if (is_array($keys[0])) {
        $keys = $keys[0];
    }

    if (!isset($array[$keys[0]])) {
        return NULL;
    }

    if (nwcount($keys) == 1) {
        $ret = $array[$keys[0]];
    }
    else {
        $keys[0] = $array[$keys[0]];
        $ret     = call_user_func_array('\zfx\aa', $keys);
    }

    return $ret;
}

// --------------------------------------------------------------------

/**
 * Array tester
 * Tests that $array is defined, IS an array AND is not empty
 *
 * @param mixed $array Array to be checked
 * @return boolean
 */
function va(&$array)
{
    if (isset($array) && is_array($array) && count($array) > 0) {
        return TRUE;
    }
    else {
        return FALSE;
    }
}

// --------------------------------------------------------------------

/**
 * Checks that $array[$key] exists AND is not empty.
 *
 * @param mixed $array Array to be checked
 * @param mixed $key Key to be tested
 * @return boolean
 */
function av($array, $key)
{
    if (is_array($array) && isset($array[$key]) && !trueEmpty($array[$key])) {
        return TRUE;
    }
    else {
        return FALSE;
    }
}

// --------------------------------------------------------------------

/**
 * Descripción: returns last key in array
 *
 * @param $array
 * @return int|null|string
 */
function lastKey($array)
{
    end($array);

    return key($array);
}

// --------------------------------------------------------------------

/**
 * Checks that $var is empty.
 *
 * @param string $var
 * @return boolean TRUE if empty (0 = not empty).
 */
function trueEmpty($var)
{
    if (is_numeric($var)) {
        return FALSE;
    }
    else {
        return empty($var);
    }
}

// --------------------------------------------------------------------

/**
 * Load base class
 *
 * @param string $className
 */
function loadBaseClass($className)
{
    $file = Config::get('basePath') . $className . '.php';
    require_once($file);
}

// --------------------------------------------------------------------

/**
 * Load controller class
 *
 * @param string $className
 */
function loadControllerClass($className)
{
    $file = Config::get('controllerPath') . $className . '.php';
    require_once($file);
}

// ------------------------------------------------------------------------

/**
 * Autocarga de clases
 * @param $className
 */
function _loadClass($className)
{
    // Separamos el espacio de nombres del nombre de la clase.
    // Bueno, aquí lo que obtenemos es la posición.
    $pos = strrpos($className, '\\');

    // Puede que haya o no haya espacio de nombres. Empezaremos por el caso afirmativo.
    if ($pos !== FALSE) {
        // Este es el espacio de nombres (podría ser compuesto)
        $namespace = substr($className, 0, $pos);
        // Esto es el fichero a intentar cargar que tiene el nombre de la clase.
        $file = substr($className, $pos + 1) . '.php';

        // --1-- Veamos si está en el directorio de sobrecargas
        $overloadPath = Config::get('overloadPath');
        if ($overloadPath != '') {
            $overloadFile = Config::get('overloadPath') . $file;
            if (file_exists($overloadFile)) {
                include_once($overloadFile);
                return;
            }
        }

        // --2-- Vamos a ver si está en un coreModule.
        if (array_key_exists($namespace, Config::get('enabledCoreModules'))) {
            $coreModules = a(Config::get('enabledCoreModules'), $namespace);
            if ($coreModules) {
                foreach ($coreModules as $module) {
                    $completePath = Config::get('basePath') . $namespace . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . $file;
                    if (file_exists($completePath)) {
                        include_once($completePath);
                        return;
                    }
                    else {
                        if (Config::get('dbSys')) {
                            $completePath = Config::get('basePath') . $namespace . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . Config::get('dbSys') . DIRECTORY_SEPARATOR . $file;
                            if (file_exists($completePath)) {
                                include_once($completePath);
                                return;
                            }
                        }
                    }
                }
            }
        }

        // --3-- Vamos a ver si está en un módulo: el espacio de nombres será el nombre del módulo.
        elseif (in_array($namespace, Config::get('enabledModules'))) {
            $completePath = Config::get('modulePath') . $namespace . DIRECTORY_SEPARATOR . Config::get('modelDir') . DIRECTORY_SEPARATOR . $file;
            if (file_exists($completePath)) {
                include_once($completePath);
                return;
            }
        }
    }
    // En caso de no haberse especificado un espacio de nombres.
    else {
        // Buscamos en el módulo actual, directorio de modelos.
        $module = (string)Config::get('moduleName');
        if ($module != '') {
            $file         = $className . '.php';
            $completePath = Config::get('modulePath') . $module . DIRECTORY_SEPARATOR . Config::get('modelDir') . DIRECTORY_SEPARATOR . $file;
            if (file_exists($completePath)) {
                include_once($completePath);
                return;
            }
        }
    }
    // Intentamos carga estándar (en CLASSPATH)
    $file = $className . '.php';
    include_once($file);
}

// --------------------------------------------------------------------

/**
 * Safe count function
 *
 * @param array or Countable $var
 *
 * @return int
 */
function nwcount($var)
{
    if (is_array($var) || $var instanceof Countable) {
        return count($var);
    }
    else {
        return 0;
    }
}

// Activamos el sistema de autocarga
spl_autoload_register('\\zfx\\_loadClass');
if (file_exists('vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    include_once('vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
}
