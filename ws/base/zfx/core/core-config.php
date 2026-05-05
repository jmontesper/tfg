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

// --------------------------------------------------------------------
// Conventions
// --------------------------------------------------------------------
$cfg['cfgDir']        = 'cfg';
$cfg['controllerDir'] = 'controllers';
$cfg['modelDir']      = 'models';
$cfg['viewDir']       = 'views';


// --------------------------------------------------------------------
// System
// --------------------------------------------------------------------
// Paths
// ALL PATHS MUST END WITH A '/'.
$cfg['appPath']        = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR;
$cfg['basePath']       = $cfg['appPath'] . 'base/';
$cfg['overloadPath']   = $cfg['appPath'] . 'overload/';
$cfg['modulePath']     = $cfg['appPath'] . 'modules/';
$cfg['libPath']        = $cfg['appPath'] . 'lib/';
$cfg['cfgPath']        = $cfg['appPath'] . $cfg['cfgDir'] . '/';
$cfg['controllerPath'] = $cfg['appPath'] . $cfg['controllerDir'] . '/';
$cfg['modelPath']      = $cfg['appPath'] . $cfg['modelDir'] . '/';
$cfg['viewPath']       = $cfg['appPath'] . $cfg['viewDir'] . '/';


// --------------------------------------------------------------------
// Deployment variables
// --------------------------------------------------------------------
// URLs
// ALL URLS MUST END WITH A '/'.
$cfg['rootUrl'] = '/';

// Server Time Zone
$cfg['timeZone'] = 'Europe/Madrid';


// --------------------------------------------------------------------
// System behavior settings
// --------------------------------------------------------------------
// Show PHP errors
$cfg['showErrors'] = FALSE;

// Default init function to be called when load controller
$cfg['defaultControllerInitFunction'] = '_init';

// Default function to be called when load controller
$cfg['defaultControllerFunction'] = '_main';

// Default controller class to be used when visiting root.
$cfg['defaultController'] = 'Index';

// Fallback controller class. If defined, use it in case of controller not found.
$cfg['fallbackController'] = '';

// Callback to use in case of be unable to find the specified controller class.
$cfg['controllerNotFoundCallback'] = '';

// Prefix for public, callable controller class.
// Use blank string '' to keep compatibility with older versions and
// include 'controllers' and 'controllers/abstract' directories in the include load path.
$cfg['controllerPrefix'] = 'Ctrl_';


// --------------------------------------------------------------------
// Configuration auto-load
// --------------------------------------------------------------------
// Example: $cfg['autoLoadConfig'] = array('modules.php', 'myappcfg.php');
$cfg['autoLoadConfig'] = NULL;


// --------------------------------------------------------------------
// Core modules (sorted by load preference)
// --------------------------------------------------------------------
$cfg['enabledCoreModules'] = array(
    'zfx' => array('core', 'dev', 'data-access')
);

// --------------------------------------------------------------------
// Modules
// --------------------------------------------------------------------
$cfg['enabledModules'] = array();


// --------------------------------------------------------------------
// Localizer
// --------------------------------------------------------------------
// Location of i18n strings
$cfg['i18nPath'] = $cfg['cfgPath'];

// Available (enabled) languages.
$cfg['languages'] = array
(
    'es',
    'en'
);

// Default language
$cfg['defaultLanguage'] = 'es';

// Locale info
$cfg['languageInfo'] = array
(
    'en' => array
    (
        'name'     => 'English',
        'true'     => 'Yes',
        'false'    => 'No',
        'null'     => '(null)',
        'dec'      => '.',
        'sep'      => ',',
        'date'     => 'm-d-Y',
        'time'     => 'h:i:s a',
        'dateTime' => 'm-d-Y h:i:s a'
    ),
    'es' => array
    (
        'name'     => 'Español',
        'true'     => 'Sí',
        'false'    => 'No',
        'null'     => '(nulo)',
        'dec'      => ',',
        'sep'      => '.',
        'date'     => 'd/m/Y',
        'time'     => 'H:i:s',
        'dateTime' => 'd/m/Y H:i:s'
    )
);

// Cache i18n sections?
$cfg['i18n_cache'] = TRUE;
