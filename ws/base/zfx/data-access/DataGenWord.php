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
class DataGenWord extends DataGen
{

    private $number = 1;
    private $blocks = array(
        'es' => array(
            array(
                'b',
                'c',
                'd',
                'f',
                'g',
                'j',
                'k',
                'l',
                'm',
                'n',
                'p',
                'r',
                's',
                't',
                'v',
                'z',
                'cr',
                'tr',
                'bl',
                'pl'
            ),
            array('a', 'e', 'i', 'o', 'u')
        )
    );

    // --------------------------------------------------------------------

    public function getNumber()
    {
        return $this->number;
    }

// --------------------------------------------------------------------

    public function setNumber($value)
    {
        $value = (int)$value;
        if ($value > 0) {
            $this->number = $value;
        }
    }

    // --------------------------------------------------------------------

    public function get($number = NULL)
    {
        if ($number !== NULL) {
            $this->setNumber($number);
        }
        $str = '';
        for ($i = 1; $i <= $this->number; $i++) {
            $str .= $this->getWord();
            if ($i < $this->number) {
                $str .= ' ';
            }
        }
        return $str;
    }

    // --------------------------------------------------------------------

    private function getWord()
    {
        $freq = mt_rand(1, 4);
        if (mt_rand(0, 100) > 80) {
            $freq += mt_rand(1, 3);
        }
        $word = '';
        for ($i = 1; $i <= $freq; $i++) {
            $b1   = array_rand($this->blocks[$this->lang][0]);
            $b2   = array_rand($this->blocks[$this->lang][1]);
            $word .= $this->blocks[$this->lang][0][$b1] . $this->blocks[$this->lang][1][$b2];
        }
        return $word;
    }
    // --------------------------------------------------------------------
}
