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
 * @package core
 */

/*
 *  Leer configuración y configurar entorno básico
 */

use zfx\Config;
use function zfx\nwcount;
use function zfx\trueEmpty;

require_once('base/zfx/core/Config.php');
Config::setup();


/*
 * cargar funciones básicas, autocarga de clases, etc.
 */
require_once('base/zfx/core/core.php');


/*
 * Predeterminados
 */
$request         = NULL;
$mod             = NULL;
$moduleDir       = '';
$controllerClass = Config::get('controllerPrefix') . Config::get('defaultController');
$rawSegments     = array();
$controllerFile  = '';
$params          = array();
$segments        = array();

/*
 * Comprobar si hay una petición y obtenerla
 */
if (isset($_GET['_seg']) && !trueEmpty($_GET['_seg'])) {
    /*
     * Atender una petición GET
     */
    $request = $_GET['_seg'];
}
else {
    /*
     * La petición también podría venir mediante la línea de comandos
     */
    if (isset($argc) && $argc == 2) {
        $request = $argv[1];
    }
}

/*
 * Si hay una petición, buscar el módulo correspondiente o bien un controlador que la atienda
  */
if ($request != '') {
    $rawSegments = explode('/', $request);
    /*
     * Si el primer segmento es el nombre un módulo que se ha definido en la configuración y además existe,
     * entonces a dicho segmento lo consideraremos un nombre de módulo
     */
    if (nwcount($rawSegments)) {
        $modules = Config::get('enabledModules');
        if (nwcount($modules)) {
            if (in_array($rawSegments[0], Config::get('enabledModules'))) {
                if (is_dir(Config::get('modulePath') . $rawSegments[0])) {
                    $moduleDir = array_shift($rawSegments);
                }
            }
        }
    }

    /*
     * Obtenemos el controlador y comprobamos el formato de su nombre
     */
    if (nwcount($rawSegments)) {
        $controllerSegment = array_shift($rawSegments);
        if (preg_match('/^[a-z][-0-9a-z]*$/u', $controllerSegment) == 1) {
            $controllerClass =
                Config::get('controllerPrefix') .
                preg_replace(
                    '/\s/u',
                    '',
                    mb_convert_case(preg_replace('/-/u', ' ', $controllerSegment), MB_CASE_TITLE, 'UTF-8')
                );
        }
    }
    $params = $rawSegments;
}


/*
 * Obtener el fichero correspondiente al controlador especificado
 */
if ($moduleDir != '') {
    /*
     * Con módulo
     */
    $controllerFile = Config::get('modulePath') . $moduleDir . DIRECTORY_SEPARATOR . Config::get('controllerDir') . DIRECTORY_SEPARATOR . $controllerClass . '.php';
}
else {
    /*
     * Sin módulo
     */
    $controllerFile = Config::get('controllerPath') . $controllerClass . '.php';
}

/*
 * Si no encontramos el fichero del controlador, intentamos usar el controlador de reserva
 */
if (!file_exists($controllerFile)) {
    $controllerClass = Config::get('fallbackController');
    $controllerFile  = Config::get('controllerPath') . $controllerClass . '.php';
    $moduleDir       = '';
}


/*
 * Si en cualquier caso no hay fichero de controlador, informar de la situación y terminar.
 */
if (!file_exists($controllerFile)) {
    $cnf = Config::get('controllerNotFoundCallback');
    if ($cnf != '' && is_callable($cnf)) {
        call_user_func($cnf, $controllerClass);
    }
    else {
        echo "Controller not found: $controllerClass";
        die;
    }
}

/*
 * Obtenemos todos los segmentos válidos (aceptamos mayúsculas como novedad), el resto los ignoramos
 */
if ($params) {
    foreach ($params as $param) {
        if (preg_match('/^[0-9a-zA-Z](?:[-0-9a-zA-Z]*?[0-9a-zA-Z])?$/u', $param) == 1) {
            $segments[] = $param;
        }
    }
}

/*
 * Guardamos en nuestra configuración el nombre del módulo en el que estamos.
 */
Config::getInstance()->set('moduleName', $moduleDir);

/*
 * Incluir el fichero con el controlador
 */
require_once($controllerFile);

/*
 * Lo instanciamos y le pasamos información esencial: los segmentos, si había, claro
 */
$controller = new $controllerClass();
$controller->_setSegments($segments);


/*
 * Los controladores pueden tener una función de inicialización/configuración que intentamos ejecutar.
 */
if (method_exists($controller, Config::get('defaultControllerInitFunction'))) {
    call_user_func(array($controller, Config::get('defaultControllerInitFunction')));
}

/*
 * Los controladores también pueden tener una función inicial que intentamos ejecutar.
 */
if (method_exists($controller, Config::get('defaultControllerFunction'))) {
    call_user_func(array($controller, Config::get('defaultControllerFunction')));
}

