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
 * HTML useful tools
 */
class HtmlTools
{

    public static function selectElement(
        $dataList,
        $name,
        $defaultKey = '',
        $blank = FALSE,
        $id = '',
        $class = '',
        $data = NULL
    )
    {

        if ($id) {
            $idAttr = "id=\"$id\"";
        }
        else {
            $idAttr = '';
        }
        if ($class) {
            $classAttr = "class=\"$class\"";
        }
        else {
            $classAttr = '';
        }
        $dataAttr = self::dataAttr($data);

        $code = "<select name=\"$name\" $idAttr $classAttr $dataAttr>";
        if ($blank) {
            if (trueEmpty($defaultKey)) {
                $selAttr = "selected=\"selected\"";
            }
            else {
                $selAttr = '';
            }
            $code .= "<option $selAttr value=\"\"></option>";
        }
        if ($dataList) {
            foreach ($dataList as $value => $caption) {
                if (!trueEmpty($defaultKey) && $defaultKey == $value) {
                    $selAttr = "selected=\"selected\"";
                }
                else {
                    $selAttr = '';
                }
                $code .= '<option ' . $selAttr . ' value="' . StrFilter::HTMLencode($value) . '">' . StrFilter::HTMLencode($caption) . "</option>";
            }
        }
        $code .= "</select>";

        return $code;
    }

    // --------------------------------------------------------------------

    public static function dataAttr(array $data = NULL)
    {
        $attr = '';
        if (va($data)) {
            foreach ($data as $key => $value) {
                $attr .= 'data-' . StrFilter::getID($key) . '="' . StrFilter::HTMLencode($value) . '" ';
            }
        }
        return $attr;
    }

    // --------------------------------------------------------------------

    public static function array2table(array $data = NULL, $tableClass = '', array $labels = NULL, $style = '')
    {
        if (!va($data)) {
            return '';
        }

        if (!trueEmpty($style)) {
            $style = 'style="' . $style . '" ';
        }
        $code = "<table class=\"$tableClass\" $style>";
        $code .= '<thead>';
        reset($data);
        $firstRow = current($data);
        if (!va($firstRow)) {
            return '';
        }

        foreach (array_keys($firstRow) as $key) {
            $code .= '<th>';
            if (!trueEmpty(a($labels, $key))) {
                $code .= StrFilter::HTMLencode(a($labels, $key));
            }
            else {
                $code .= StrFilter::HTMLencode($key);
            }
            $code .= '</th>';
        }
        $code .= '</thead>';
        $code .= '<tbody>';
        foreach ($data as $row) {
            $code .= '<tr>';
            if (va($row)) {
                foreach ($row as $value) {
                    $code .= '<td>';
                    $code .= StrFilter::HTMLencode($value);
                    $code .= '</td>';
                }
            }
            $code .= '</tr>';
        }
        $code .= '</tbody>';
        $code .= '</table>';

        return $code;
    }

    // --------------------------------------------------------------------

    public static function array2list(array $data = NULL, $listClass = '', $style = '')
    {
        if (!va($data)) {
            return '';
        }

        if (!trueEmpty($style)) {
            $style = 'style="' . $style . '" ';
        }
        $code = "<ul class =\"$listClass\" $style>";
        foreach ($data as $key => $value) {
            $code .= "<li>";
            $code .= "<a href=\"http://$value \">
                    $key 
             </a>
            </li>";

        }
        $code .= "</ul>";
        return $code;

    }

    // --------------------------------------------------------------------
}
