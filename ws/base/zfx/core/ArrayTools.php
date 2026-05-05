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
 * Array useful tools
 */
class ArrayTools
{

    /**
     * Convertir un array desde UTF-8 a la codificación especificada
     */
    static function transcode($encTo, array $arrayOriginal = NULL, $encFrom = 'UTF-8')
    {
        return array_map(function ($valor) use ($encTo, $encFrom) {
            return mb_convert_encoding($valor, $encTo, $encFrom);
        }, $arrayOriginal);
    }

    // --------------------------------------------------------------------

    static function renameKeys(array &$arrayOriginal, array $pares = NULL)
    {
        if (!$pares) {
            return;
        }
        foreach ($pares as $claveVieja => $claveNueva) {
            $arrayOriginal[$claveNueva] = a($arrayOriginal, $claveVieja);
            if (array_key_exists($claveVieja, $arrayOriginal)) {
                unset($arrayOriginal[$claveVieja]);
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Devuelve una copia del array que se le pasa, con todas las claves prefijadas.
     *
     * @param array $arrayOriginal
     * @param $prefijo
     * @return void
     */
    static function prefixKeys(array $arrayOriginal = NULL, $prefijo)
    {
        $ret = [];
        if ($arrayOriginal) {
            foreach ($arrayOriginal as $k => $v) {
                $ret[$prefijo . $k] = $v;
            }
        }
        return $ret;
    }

    // --------------------------------------------------------------------

    /**
     * A partir de una lista de números y un valor V,
     * devuelve el primer valor de la lista que sea mayor que V.
     * @param $value Valor V
     * @param array $numList Lista numérica
     * @return mixed|NULL
     */
    static function findFirstGreatest($value, array $numList = NULL)
    {
        if ($numList) {
            foreach ($numList as $num) {
                if ($num > $value) {
                    return $num;
                }
            }
        }
        return NULL;
    }

    // --------------------------------------------------------------------

    static function prefixValues(array &$arrayOriginal = NULL, $prefix)
    {
        if (!$arrayOriginal) {
            return;
        }
        foreach ($arrayOriginal as &$value) {
            $value = $prefix . $value;
        }
    }

    // --------------------------------------------------------------------


}
