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

/**
 * Database basic services for PostgreSQL
 */
class PgDB
{

    /**
     * PostgreSQL resource of last connection
     * @var resource $resConn
     */
    protected $resConn;

    /**
     * Result of last operation
     * @var resource
     */
    protected $lastRes;

    /**
     * Ignore errors flag
     * @var boolean
     */
    protected $ignoreErrors;

    // --------------------------------------------------------------------

    /**
     * Constructor
     * @param string $profile Database configuration profile
     */
    public function __construct($profile = NULL)
    {
        if ($profile == NULL | trueEmpty($profile)) {
            $profile = Config::get('dbProfile');
        }

        if (!a(Config::get('pg'), $profile)) {
            Debug::devError("Unknown DB profile: '$profile'.");
        }

        $str                = 'host=' . a(a(Config::get('pg'), $profile), 'dbHost');
        $str                .= ' port=' . a(a(Config::get('pg'), $profile), 'dbPort');
        $str                .= ' dbname=' . a(a(Config::get('pg'), $profile), 'dbDatabase');
        $str                .= ' user=' . a(a(Config::get('pg'), $profile), 'dbUser');
        $str                .= ' password=' . a(a(Config::get('pg'), $profile), 'dbPass');
        $str                .= ' options=\'--client_encoding=UTF8\'';
        $this->resConn      = pg_connect($str);
        $this->lastRes      = NULL;
        $this->ignoreErrors = (bool)a(a(Config::get('pg'), $profile), 'ignoreErrors');

        if ($this->resConn == FALSE) {
            Debug::devError("Postgresql connection error.");
        }
    }

    // --------------------------------------------------------------------

    /**
     * Escape string for use in a SQL query as a string value
     *
     * @param string $txt String to be escaped
     * @return string Escaped string
     */
    public static function escape($txt)
    {
        return pg_escape_string($txt);
    }

    // --------------------------------------------------------------------

    /**
     * Quote string for use in a SQL query as a column name.
     *
     * @param string $txt String to be quoted
     * @return string Quoted string
     */
    public static function quote($txt)
    {
        return '"' . preg_replace('/"/u', '""', $txt) . '"';
    }

    // --------------------------------------------------------------------

    /**
     * Parse the representation string of a PostgreSQL array
     *
     * @param string $str
     * @param string $delimiter
     * @return array
     */
    public static function parseArray($str, $delimiter = ',')
    {
        $chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
        $res   = array();

        $len    = nwcount($chars);
        $qopen  = FALSE;
        $value  = '';
        $escape = FALSE;
        for ($i = 1; $i < $len - 1; $i++) {
            if ($chars[$i] == '\\') {
                $escape = TRUE;
                continue;
            }
            if ($chars[$i] == '"') {
                if ($escape == FALSE) {
                    if ($qopen == FALSE) {
                        $qopen = TRUE;
                    }
                    else {
                        $qopen = FALSE;
                    }
                    continue;
                }
                else {
                    $escape = FALSE;
                }
            }
            if ($chars[$i] == $delimiter) {
                if ($qopen == FALSE) {
                    $res[] = $value;
                    $value = '';
                    continue;
                }
            }

            $value  .= $chars[$i];
            $escape = FALSE;
        }
        if ($value) {
            $res[] = $value;
        }
        return ($res);
    }

    // --------------------------------------------------------------------

    /**
     * Query Multiple
     *
     * Execute multiple query. No rows will be returned.
     *
     * @param string $query Query to be executed
     * @return boolean TRUE on success
     */
    public function qm($query)
    {
        return $this->q($query);
    }

    // --------------------------------------------------------------------

    /**
     * Query
     *
     * Simple query. No rows will be returned.
     *
     * @param string $query Query to be executed
     * @param boolean $returnRes if TRUE, return pg_query result
     * @return boolean TRUE on success
     */
    public function q($query, $returnRes = FALSE)
    {
        $res = pg_query($this->resConn, $query);
        if ($returnRes) {
            return $res;
        }
        if (!$res) {
            if ($this->ignoreErrors == FALSE || pg_connection_status($this->resConn) !== PGSQL_CONNECTION_OK) {
                $this->queryError($query);
            }
            return FALSE;
        }
        $this->lastRes = NULL;
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
        $errorText = pg_last_error($this->resConn);
        Debug::show("PostgreSQL query error: " .
                    "'$errorText'\nQuery:\n$query");
        echo "<pre>";
        debug_print_backtrace();
        die;
    }

    // --------------------------------------------------------------------

    public function getLastError()
    {
        return pg_last_error($this->resConn);
    }

    // --------------------------------------------------------------------

    /**
     * Query and get Row
     *
     * Execute query and return the first row as map array or a single value.
     *
     * @param string $query Query to be executed
     * @param string $column Optional column name for retrieving a single value.
     */
    public function qr($query, $column = NULL)
    {
        $this->lastRes = NULL;
        $res           = pg_query($this->resConn, $query);
        if (!$res) {
            if ($this->ignoreErrors == FALSE || pg_connection_status($this->resConn) !== PGSQL_CONNECTION_OK) {
                $this->queryError($query);
            }
            return NULL;
        }
        else {
            if (pg_num_rows($res) > 0) {
                $row = pg_fetch_assoc($res, 0);
                if (!$column) {
                    return $row;
                }
                else {
                    return a($row, $column);
                }
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
     * @param string $pack Use value of column "$pack" as array value.
     */
    public function qa($query, $key = NULL, $pack = '')
    {
        $this->lastRes = NULL;
        $res           = pg_query($this->resConn, $query);
        if (!$res) {
            if ($this->ignoreErrors == FALSE || pg_connection_status($this->resConn) !== PGSQL_CONNECTION_OK) {
                $this->queryError($query);
            }
            return NULL;
        }
        else {
            $rows = pg_num_rows($res);
            if ($rows > 0) {
                // If $key is NULL, return a simple array
                if (!$key) {
                    return pg_fetch_all($res);
                } // Or create a custom array
                else {
                    $result = array();
                    for ($i = 0; $i < $rows; $i++) {
                        $row = pg_fetch_assoc($res, $i);
                        if ($pack) {
                            $result[a($row, $key)] = a($row, $pack);
                        }
                        else {
                            $result[a($row, $key)] = $row;
                        }
                    }
                    return $result;
                }
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
        $this->lastRes = NULL;
        $res           = pg_query($this->resConn, $query);
        if (!$res) {
            if ($this->ignoreErrors == FALSE || pg_connection_status($this->resConn) !== PGSQL_CONNECTION_OK) {
                $this->queryError($query);
            }
            return NULL;
        }
        else {
            $this->lastRes = $res;
            return $this;
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
            return pg_fetch_assoc($this->lastRes);
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

    /**
     * Get pg_ connection handler
     *
     * @return resource
     */
    public function getConnection()
    {
        return $this->resConn;
    }

    // --------------------------------------------------------------------
}
