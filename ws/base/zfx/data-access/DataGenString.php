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
 * Human name generator
 */
class DataGenString extends DataGen
{

    const ALPHA   = 'abcdefghijklmnopqrstuvwxyz';
    const NUMERIC = '0123456789';
    const SYMBOL  = '!"#$%&\'()*+,-./:;<=>?@[]^_`{|}~\\';
    const UNICODE = 'ص❤щ乗┴ɱ↗►⅔μé∉⢄⁋☢€⬡⌛';

    protected $chars   = '';
    protected $maxSize = 1;
    protected $minSize = 1;

    // --------------------------------------------------------------------

    public function __construct($lang = NULL)
    {
        parent::__construct($lang);
        $this->setChars(self::ALPHA . self::NUMERIC);
    }

    // --------------------------------------------------------------------


    public function getChars()
    {
        return $this->chars;
    }

// --------------------------------------------------------------------

    public function setChars($value)
    {
        $this->chars = $value;
    }

    // --------------------------------------------------------------------

    public function getMinSize()
    {
        return $this->minSize;
    }

    // --------------------------------------------------------------------

    public function setMinSize($value)
    {
        $value = (int)$value;
        if ($value < 1) {
            $value = 1;
        }
        $this->minSize = (int)$value;
        if ($this->minSize > $this->maxSize) {
            $this->maxSize = $this->minSize;
        }
    }

    // --------------------------------------------------------------------

    public function getMaxSize()
    {
        return $this->maxSize;
    }

    // --------------------------------------------------------------------

    public function setMaxSize($value)
    {
        $value = (int)$value;
        if ($value < 1) {
            $value = 1;
        }
        $this->maxSize = (int)$value;
        if ($this->maxSize < $this->minSize) {
            $this->minSize = $this->maxSize;
        }
    }

    // --------------------------------------------------------------------

    public function get($minSize = NULL, $maxSize = NULL, $chars = NULL)
    {
        if ($minSize !== NULL) {
            $this->setMinSize($minSize);
        }
        if ($maxSize !== NULL) {
            $this->setMaxSize($maxSize);
        }
        if ($chars !== NULL) {
            $this->setChars($chars);
        }
        $maxOffset = mb_strlen($this->chars, 'UTF-8') - 1;
        if ($this->maxSize == $this->minSize) {
            $limit = $this->maxSize;
        }
        else {
            $limit = mt_rand($this->minSize, $this->maxSize);
        }
        $str = '';
        for ($i = 1; $i <= $limit; $i++) {
            $str .= mb_substr($this->chars, mt_rand(0, $maxOffset), 1, 'UTF-8');
        }
        return $str;
    }
    // --------------------------------------------------------------------
}
