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
 * Debug class
 */
class Debug
{

    /**
     * Show data and die
     *
     * @see show()
     */
    public static function showDie($data = NULL, $debugLevel = 0)
    {
        self::show($data, $debugLevel);
        die;
    }

    // --------------------------------------------------------------------

    /**
     * Show data: print $data and record it in a log file.
     *
     * @param integer $debugLevel Debug level:
     *
     * 0 = always print data.
     * If between N and 0<N<L (where L is the value of Config::sys('debugLevel')) then:
     * if Config::sys('debugLog') is TRUE then log $data in file 'debugN'.
     * If Config::sys('debugShow') is TRUE then show $data too.
     */
    public static function show($data = NULL, $debugLevel = 0, $forceScroll = TRUE)
    {
        if (is_bool($data)) {
            $data = $data ? 'true' : 'false';
        }

        if ($debugLevel == 0) {
            $txt = print_r($data, TRUE);
            self::echoMessage($txt, $forceScroll);
        }
        else {
            if ($debugLevel <= Config::get('debugLevel')) {
                $txt = print_r($data, TRUE);
                if (Config::get('debugLog')) {
                    Log::log($txt, 'debug' . $debugLevel);
                }
                if (Config::get('debugShow')) {
                    self::echoMessage($txt, $forceScroll);
                }
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Show formatted message. Useful for debugging purposes.
     *
     * @param type $txt Text to be shown
     */
    protected static function echoMessage($txt, $forceScroll = FALSE)
    {
        if ($forceScroll) {
            echo "<div style=\"overflow: scroll;\">";
        }
        $borderColor = sprintf('#%02X%02X%02X', rand(0, 200), rand(0, 200), rand(0, 200));
        echo "<pre style=\"background-color: white; color: #505050; " .
             "border: 2px solid $borderColor; padding: 5px;\">";
        echo htmlspecialchars($txt);
        echo '</pre>';
        if ($forceScroll) {
            echo "</div>";
        }

    }

    // --------------------------------------------------------------------

    /**
     * Data analysis
     * This function is like show() but it prints complete info about $data.
     *
     * @see show()
     */
    public static function analyze($data, $debugLevel = 0)
    {
        if ($debugLevel == 0) {
            ob_start();
            var_dump($data);
            $dump = ob_get_contents();
            ob_end_clean();
            self::echoMessage($dump);
        }
        else {
            if ($debugLevel <= $cfg->debugLevel) {
                ob_start();
                var_dump($data);
                $dump = ob_get_contents();
                ob_end_clean();
                if (Config::get('debugLog')) {
                    Log::log($dump, 'debug' . $debugLevel);
                }
                if (Config::get('debugShow')) {
                    self::echoMessage($dump);
                }
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Show formatted message like BSOD: die.
     */
    public static function devError($data)
    {
        $txt = print_r($data, TRUE);
        Log::log($data, 'error.log');
        $borderColor = sprintf("#%02X%02X%02X", rand(0, 200), rand(0, 200), rand(0, 200));
        echo "<pre style=\"background-color: white; color: #505050; " .
             "border: 2px dashed $borderColor; padding: 5px;\">";
        echo htmlspecialchars($txt);
        echo '</pre>';
        die;
    }

    // --------------------------------------------------------------------
}
