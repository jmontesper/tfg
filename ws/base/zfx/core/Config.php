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

namespace zfx;

use function array_merge;

/**
 * Configuration system class
 */
class Config
{

    // Singleton instance
    private static $instance = NULL;
    // Store configuration data here
    private $sysConfig = array();

    // --------------------------------------------------------------------

    /**
     * Setup and initialize system
     */
    public static function setup()
    {
        // 1. First, we must load and reload (overwriting when needed) all configuration available

        // Load CORE config file
        self::loadConfig(__DIR__ . DIRECTORY_SEPARATOR . 'core-config.php', FALSE);

        // Load CORE user settings
        self::loadConfig('core-config');

        // Set INCLUDE_PATH and read enabled modules configuration
        if (self::get('enabledCoreModules')) {
            foreach (self::get('enabledCoreModules') as $namespace => $moduleList) {
                if ($moduleList) {
                    foreach ($moduleList as $module) {
                        if ($module != 'core') { // Skip CORE config
                            self::loadConfig(Config::get('basePath') . $namespace . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'module-config.php',
                                FALSE);
                        }
                    }
                }
            }
        }

        // Load user configuration files
        if (is_array(self::get('autoLoadConfig'))) {
            foreach (self::get('autoLoadConfig') as $file) {
                self::loadConfig($file);
            }
        }

        // 2. Now we can apply all the settings.

        // Previous include path configuration
        // DB subdirs
        if (Config::get('dbSys')) {
            $dbAwareModelDir = Config::get('modelPath') . Config::get('dbSys') . PATH_SEPARATOR;
        }
        else {
            $dbAwareModelDir = '';
        }

        // Controller directory management
        // If controllerPrefix is blank, behave as old versions.
        if (Config::get('controllerPrefix') == '') {
            $controllerDir = Config::get('controllerPath') . PATH_SEPARATOR . Config::get('controllerPath') . 'abstract' . DIRECTORY_SEPARATOR . PATH_SEPARATOR;
        }
        else {
            $controllerDir = '';
        }

        // Define our include_path
        set_include_path(
            get_include_path() .
            PATH_SEPARATOR .
            $controllerDir .
            Config::get('modelPath') .
            PATH_SEPARATOR .
            $dbAwareModelDir .
            Config::get('libPath')
        );

        // Error reporting, of course
        if (!self::get('showErrors')) {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
        else {
            error_reporting(E_ERROR);
            ini_set('display_errors', '1');
        }

        // Set timezone
        date_default_timezone_set(self::get('timeZone'));

    }

    // --------------------------------------------------------------------

    /**
     * Load configuration data from $file
     *
     * @param string $file File name (without .php extension if $computePath is TRUE)
     * @param boolean $computePath If TRUE, use system settings directory. If FALSE, $file should be a complete path specification
     */
    public static function loadConfig($file, $computePath = TRUE)
    {
        $instance = self::getInstance();
        if ($computePath) {
            $path = $instance->sysConfig['cfgPath'] . $file . '.php';
        }
        else {
            $path = $file;
        }
        if (file_exists($path)) {
            include($path);
            if (isset($cfg)) {
                $instance->sysConfig = array_merge($instance->sysConfig, $cfg);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get instance of Config class
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    // --------------------------------------------------------------------

    /**
     * Get configuration data by key
     *
     * @param string $key Data key
     * @return mixed
     */
    public static function get($key)
    {
        // Function a() still not accesible here
        if (isset(self::getInstance()->sysConfig[$key])) {
            return self::getInstance()->sysConfig[$key];
        }
        return NULL;
    }

    // --------------------------------------------------------------------

    /**
     * Set configuration data by key
     *
     * @param string $key Data key
     * @param string $value Data value
     */
    public static function set($key, $value)
    {
        self::getInstance()->sysConfig[$key] = $value;
    }

    // --------------------------------------------------------------------

    /**
     * Esto es la combinación de devolver rootUrl y moduleName terminado en barra
     *
     * @return mixed|null
     */
    public static function getModRootUrl()
    {
        $r = '';
        if (array_key_exists('rootUrl', self::$instance->sysConfig)) {
            $r = self::getInstance()->sysConfig['rootUrl'];
        }
        if (array_key_exists('moduleName', self::$instance->sysConfig)) {
            if (self::$instance->sysConfig['moduleName'] != '') {
                $r .= self::getInstance()->sysConfig['moduleName'] . '/';
            }
        }
        return $r;
    }

}
