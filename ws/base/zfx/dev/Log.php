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
 * @package dev
 */

namespace zfx;

/**
 * Log class
 *
 * Logfile management
 */
class Log
{

    /**
     * Add entry to log file
     *
     * @param mixed $data Entry data
     * @param string $fileName Log file name. If blank, 'dev.log' will be used.
     */
    public static function log($data, $filename = 'dev.log')
    {
        if (!Config::get('logPath')) {
            return;
        }
        if (!is_string($data)) {
            $data = print_r($data, TRUE);
        }
        $def = @fopen(Config::get('logPath') . $filename, 'a');
        if ($def !== FALSE) {
            $now = gettimeofday();
            fprintf($def, "[%s.%06u]\n%s\n", date('Y-m-d H:i:s', $now['sec']), $now['usec'], $data);
            fclose($def);
        }
    }

    // --------------------------------------------------------------------

    public static function delete($filename = 'dev.log')
    {
        if (!Config::get('logPath')) {
            return;
        }
        if (is_writable(Config::get('logPath') . $filename)) {
            unlink(Config::get('logPath') . $filename);
        }
    }

    // --------------------------------------------------------------------
}
