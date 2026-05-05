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
 * @package data-access
 */

namespace zfx;

use mysqli;
use mysqli_result;

/**
 * Basic database services for MySQL
 */
class MyDB extends mysqli
{

    /**
     * Last result
     *
     * @var mysqli_result $lastRes
     */
    protected $lastRes;

    /**
     * Ignore errors flag
     *
     * @var boolean
     */
    protected $ignoreErrors;

    // --------------------------------------------------------------------

    /**
     * Constructor
     *
     * @param string $profile Database configuration profile
     */
    public function __construct($profile = NULL)
    {
        if ($profile == NULL | trueEmpty($profile)) {
            $profile = Config::get('dbProfile');
        }

        if (!a(Config::get('my'), $profile)) {
            Debug::devError("Unknown DB profile: '$profile'.");
        }

        parent::__construct('p:' . a(a(Config::get('my'), $profile), 'dbHost'),
            a(a(Config::get('my'), $profile), 'dbUser'), a(a(Config::get('my'), $profile), 'dbPass'),
            a(a(Config::get('my'), $profile), 'dbDatabase'), a(a(Config::get('my'), $profile), 'dbPort'));
        if ($this->connect_error) {
            $errorCode = $this->connect_errno;
            $errorText = $this->connect_error;
            Debug::devError("MySQL connection error: (# $errorCode) '$errorText'");
        }
        $this->set_charset('utf8');
        $this->ignoreErrors = (bool)a(a(Config::get('my'), $profile), 'ignoreErrors');
        $this->clearLastRes();
    }

    // --------------------------------------------------------------------

    /**
     * Clear last result
     */
    private function clearLastRes()
    {
        if ($this->lastRes) {
            $this->lastRes->free();
        }
        $this->lastRes = NULL;
    }

    // --------------------------------------------------------------------

    /**
     * Escape string for use in a SQL query as a string value
     *
     * @param string $txt String to be escaped
     *
     * @return string Escaped string
     */
    public static function escape($txt)
    {
        return StrFilter::escapeMySQL($txt);
    }

    // --------------------------------------------------------------------

    /**
     * Quote string for use in a SQL query as a column name.
     *
     * @param string $txt String to be quoted
     *
     * @return string Quoted string
     */
    public static function quote($txt)
    {
        return '`' . preg_replace('/`/u', '``', $txt) . '`';
    }

    // --------------------------------------------------------------------

    /**
     * Query
     *
     * Simple query. No rows will be returned.
     *
     * @param string $query Query to be executed
     *
     * @return boolean TRUE on success
     */
    public function q($query)
    {
        $res = $this->query($query);
        if (!$res) {
            if ($this->ignoreErrors) {
                return FALSE;
            }
            $this->queryError($query);
        }
        $obj = $this->store_result();
        if ($obj) {
            $obj->free_result();
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Process and show last error.
     *
     * Usually this function terminates script execution.
     *
     * @param string $query Failed query
     */
    public function queryError($query)
    {
        $this->clearLastRes();
        $errorCode = $this->errno;
        $errorText = $this->error;
        Debug::show("MySQL query error: (# $errorCode) " .
                    "'$errorText'\nQuery:\n$query");
        echo "<pre>";
        debug_print_backtrace();
        die;
    }

    // --------------------------------------------------------------------

    /**
     * Query Multiple
     *
     * Execute multiple query. No rows will be returned.
     *
     * @param string $query Query to be executed
     *
     * @return boolean TRUE on success
     */
    public function qm($query)
    {
        $res = $this->multi_query($query);
        if (!$res) {
            if ($this->ignoreErrors) {
                return;
            }
            $this->queryError("\nDetected in statement #1:\n" . $query);
        }
        else {
            $obj = $this->store_result();
            if ($obj) {
                $obj->free_result();
            }
            $i = 1;
            while ($this->more_results()) {
                $i++;
                $nextRes = $this->next_result();
                if (!$nextRes) {
                    if ($this->ignoreErrors) {
                        return;
                    }
                    $this->queryError("\nDetected in statement #$i:\n" . $query);
                }
                $obj = $this->store_result();
                if ($obj) {
                    $obj->free_result();
                }
            }
        }
    }

    // --------------------------------------------------------------------

    public function getLastError()
    {
        return ''; // TODO
    }

    // --------------------------------------------------------------------


    /**
     * Query and get Row
     *
     * Execute query and return the first row as map array or a single value.
     *
     * @param string $query Query to be executed
     * @param string $column Optional column name for retrieving a single value.
     *
     * @return array|mixed|null|void
     *
     */
    public function qr($query, $column = NULL)
    {
        $this->clearLastRes();
        $res = $this->query($query);
        if (!$res) {
            if ($this->ignoreErrors) {
                return;
            }
            $this->queryError($query);
        }
        else {
            if ($res !== TRUE) {
                if (!$column) {
                    $ret = $res->fetch_assoc();
                }
                else {
                    $ret = a($res->fetch_assoc(), $column);
                }
                $res->free();
                return $ret;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Query and get Array
     *
     * Execute query and return all rows as an array.
     *
     * @param string $query Query to be executed
     * @param string $key Use value of column "$key" as array keys.
     * @param string $pack Use value of column "$pack" as array values.
     *
     * @return array|mixed|null|void
     */
    public function qa($query, $key = NULL, $pack = '')
    {
        $this->clearLastRes();
        $res = $this->query($query);
        if (!$res) {
            if ($this->ignoreErrors) {
                return;
            }
            $this->queryError($query);
        }
        else {
            if ($res !== TRUE) {
                $result = array();
                do {
                    $row = $res->fetch_array(MYSQLI_ASSOC);
                    if ($row) {
                        // If $key is NULL, return a simple array
                        if (!$key) {
                            $result[] = $row;
                        }
                        else {
                            // Else return a custom array
                            if ($pack) {
                                $result[a($row, $key)] = a($row, $pack);
                            }
                            else {
                                $result[a($row, $key)] = $row;
                            }
                        }
                    }
                } while ($row);
                $res->free();
                return $result;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Query and Store
     *
     * Execute query and store the results staying ready for iterating.
     * Returns nothing: to iterate use DB object.
     *
     * @param string $query La consulta a ejecutar
     *
     * @see next()
     */
    public function qs($query)
    {
        $this->clearLastRes();
        $res = $this->query($query);
        if (!$res) {
            if ($this->ignoreErrors) {
                return;
            }
            $this->queryError($query);
        }
        else {
            if ($res !== TRUE) {
                $this->lastRes = $res;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get next row of a stored result as a array
     *
     * @return array
     *
     * @see qs()
     */
    public function next()
    {
        if ($this->lastRes) {
            $ret = $this->lastRes->fetch_array(MYSQLI_ASSOC);
            if (!$ret) {
                $this->clearLastRes();
            }
            return $ret;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get error reporting behaviour status
     *
     * @return bool
     */
    public function getIgnoreErrors()
    {
        return $this->ignoreErrors;
    }

    // --------------------------------------------------------------------

    /**
     * Set error reporting behaviour status
     *
     * @param bool $val if TRUE, set error reporting ON
     */
    public function setIgnoreErrors($val)
    {
        $this->ignoreErrors = (bool)$val;
    }

    // --------------------------------------------------------------------
}
