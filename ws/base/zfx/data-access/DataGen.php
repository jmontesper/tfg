<?php
/*
  Zerfrex (R) RAD ADM
  Zerfrex RAD for Administration & Data Management
  Copyright (c) 2013-2022 by Jorge A. Montes Pérez <jorge@zerfrex.com>
  All rights reserved. Todos los derechos reservados.

  Este software solo se puede usar bajo licencia del autor.
  El uso de este software no implica ni otorga la adquisición de
  derechos de explotación ni de propiedad intelectual o industrial.
 */

/**
 * @package data-gen
 */

namespace zfx;

/**
 * Basic string generator
 */
abstract class DataGen
{

    protected $lang;

    public function __construct($lang = NULL)
    {
        $this->setLang($lang);
        $this->str = NULL;
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
    }
}
