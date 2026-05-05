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

/**
 * Function provider class of UTF-8 string validation
 */
class StrValidator
{
    // --------------------------------------------------------------------

    /**
     * Test a YYYY-MM-DD date
     *
     * @param string $date Date string to be checked
     * @return boolean TRUE if OK
     */
    public static function dateISO($date)
    {
        $fields = array();
        $r      = preg_match('/([0-9]{4})-([0-1][0-9])-([0-3][0-9])/u', $date, $fields);
        if ($r !== 1) {
            return FALSE;
        }
        else {
            return checkdate($fields[2], $fields[3], $fields[1]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Test a HH:MM:SS time
     *
     * @param string $time Time string to be checked
     * @return boolean TRUE if OK
     */
    public static function timeISO($time)
    {
        $r = preg_match('/([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])/u', $time);
        if ($r !== 1) {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Test a YYYY-MM-DD HH:MM:SS datetime string
     *
     * @param string $dateTime Date string to be checked
     * @return boolean TRUE if OK
     */
    public static function dateTimeISO($dateTime)
    {
        $fields = array();
        $r      = preg_match('/([0-9]{4})-([0-1][0-9])-([0-3][0-9]) ([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])/u',
            $dateTime, $fields);
        if ($r !== 1) {
            return FALSE;
        }
        else {
            return checkdate($fields[2], $fields[3], $fields[1]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * View section ID name checker
     * @param string $str Name of ID
     * @return boolean
     */
    public static function viewSectionID($str)
    {
        $result = preg_match('/^[a-z0-9](-?[a-z0-9]+)*$/u', $str);
        if ($result == 1) {
            return (TRUE);
        }
        else {
            return (FALSE);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Page number format checker
     *
     * @param string $str String containing the page number expression.
     * @return boolean
     */
    public static function pageNumber($str)
    {
        $result = preg_match('/^[0-9]+$/u', $str);
        if ($result == 1) {
            return (TRUE);
        }
        else {
            return (FALSE);
        }
    }

    // --------------------------------------------------------------------

    /**
     * URL segment format checker
     *
     * @param string str Segment to be checked
     * @return boolean
     */
    public static function segFunction($str)
    {
        $str    = (string)$str;
        $result = preg_match('/^[a-z]+[-a-z0-9]*$/u', $str);
        if ($result == 1) {
            return (TRUE);
        }
        else {
            return (FALSE);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Powerless email validation
     *
     * @param string $str Email address
     * @return boolean
     */
    public static function email($str)
    {
        $result = preg_match('/^\S+@\S+$/u', $str);
        if ($result == 1) {
            return (TRUE);
        }
        else {
            return (FALSE);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Numeric nnnn.nnn validation
     *
     * @param string $str Number to be checked
     * @return boolean
     */
    public static function numeric($str)
    {
        $result = preg_match('/-?[0-9]+(?:\.[0-9]+)?/u', $str);
        if ($result == 1) {
            return (TRUE);
        }
        else {
            return (FALSE);
        }
    }

    // --------------------------------------------------------------------
}
