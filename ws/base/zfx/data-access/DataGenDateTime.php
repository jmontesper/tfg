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
class DataGenDateTime extends DataGen
{

    const DATETIME = 0;
    const DATE     = 1;
    const TIME     = 2;

    private $type       = 0;
    private $dayStart   = 1;
    private $dayEnd     = 28;
    private $monthStart = 1;
    private $monthEnd   = 12;
    private $yearStart  = 1990;
    private $yearEnd    = 2013;
    private $hourStart  = 0;
    private $hourEnd    = 23;
    private $minStart   = 0;
    private $minEnd     = 59;
    private $secStart   = 0;
    private $secEnd     = 59;

    // --------------------------------------------------------------------

    public function getType()
    {
        return $this->type;
    }

    // --------------------------------------------------------------------

    public function setType($value)
    {
        $this->type = (int)$value;
    }

    // --------------------------------------------------------------------

    public function getHourStart()
    {
        return $this->hourStart;
    }

    // --------------------------------------------------------------------

    public function setHourStart($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 23) {
            $this->hourStart = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getMinStart()
    {
        return $this->minStart;
    }

    // --------------------------------------------------------------------

    public function setMinStart($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 59) {
            $this->minStart = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getSecStart()
    {
        return $this->secStart;
    }

    // --------------------------------------------------------------------

    public function setSecStart($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 59) {
            $this->secStart = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getHourEnd()
    {
        return $this->hourEnd;
    }

    // --------------------------------------------------------------------

    public function setHourEnd($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 23) {
            $this->hourEnd = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getMinEnd()
    {
        return $this->minEnd;
    }

    // --------------------------------------------------------------------

    public function setMinEnd($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 59) {
            $this->minEnd = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getSecEnd()
    {
        return $this->secEnd;
    }

    // --------------------------------------------------------------------

    public function setSecEnd($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 59) {
            $this->secEnd = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getYearStart()
    {
        return $this->yearStart;
    }

    // --------------------------------------------------------------------

    public function setYearStart($value)
    {
        $this->yearStart = (int)$value;
    }

    // --------------------------------------------------------------------

    public function getMonthStart()
    {
        return $this->monthStart;
    }

    // --------------------------------------------------------------------

    public function setMonthStart($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 12) {
            $this->monthStart = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getDayStart()
    {
        return $this->dayStart;
    }

    // --------------------------------------------------------------------

    public function setDayStart($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 28) {
            $this->dayStart = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getYearEnd()
    {
        return $this->yearEnd;
    }

    // --------------------------------------------------------------------

    public function setYearEnd($value)
    {
        $this->yearEnd = (int)$value;
    }

    // --------------------------------------------------------------------

    public function getMonthEnd()
    {
        return $this->monthEnd;
    }

    // --------------------------------------------------------------------

    public function setMonthEnd($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 12) {
            $this->monthEnd = $value;
        }
    }

    // --------------------------------------------------------------------

    public function getDayEnd()
    {
        return $this->dayEnd;
    }

    // --------------------------------------------------------------------

    public function setDayEnd($value)
    {
        $value = (int)$value;
        if ($value >= 0 && $value <= 28) {
            $this->dayEnd = $value;
        }
    }

    // --------------------------------------------------------------------

    public function get($type = NULL)
    {
        if ($type !== NULL) {
            $this->setType($type);
        }
        $part1 = '';
        $part2 = '';
        $part3 = '';
        if ($this->type == self::DATETIME || $this->type == self::DATE) {
            $part1 = sprintf('%04d-%02d-%02d',
                mt_rand(min($this->yearStart, $this->yearEnd), max($this->yearStart, $this->yearEnd)),
                mt_rand(min($this->monthStart, $this->monthEnd), max($this->monthStart, $this->monthEnd)),
                mt_rand(min($this->dayStart, $this->dayEnd), max($this->dayStart, $this->dayEnd))
            );
        }
        if ($this->type == self::DATETIME || $this->type == self::TIME) {
            $part3 = sprintf('%02d:%02d:%02d',
                mt_rand(min($this->hourStart, $this->hourEnd), max($this->hourStart, $this->hourEnd)),
                mt_rand(min($this->minStart, $this->minEnd), max($this->minStart, $this->minEnd)),
                mt_rand(min($this->secStart, $this->secEnd), max($this->secStart, $this->secEnd))
            );
        }
        if ($part1 && $part3) {
            $part2 = ' ';
        }
        return $part1 . $part2 . $part3;
    }
    // --------------------------------------------------------------------
}
