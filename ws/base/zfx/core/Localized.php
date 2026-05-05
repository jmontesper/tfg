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
 * @package app
 */

namespace zfx;

/**
 * Localized base class for all objects that need to make use of a localizer
 */
abstract class Localized
{

    /**
     * It's very important to keep this variable as PRIVATE so derived
     * classes must use the access methods. It will avoid innecesary loads.
     *
     * @var Localizer $localizer
     */
    private $localizer = NULL;

    // --------------------------------------------------------------------

    /**
     * Get Localizer instance
     *
     * @return Localizer
     */
    public function getLocalizer()
    {
        // The first time we need a localizer we'll instantiate it.
        if (!$this->localizer) {
            $this->localizer = new Localizer();
        }
        return $this->localizer;
    }

    // --------------------------------------------------------------------

    /**
     * Set Localizer instance
     *
     * @param Localizer $value
     */
    public function set_localizer(Localizer $value = NULL)
    {
        $this->localizer = $value;
    }

    // --------------------------------------------------------------------
}
