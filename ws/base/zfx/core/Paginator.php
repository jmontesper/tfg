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
 * This class represents a navigation and pagination bar.
 */
class Paginator
{

    /**
     * Current number items available
     * @var integer $numItems
     */
    private $numItems;

    /**
     * Current number of items per page set
     * @var integer $numItemsPerPage
     */
    private $numItemsPerPage;

    /**
     * Current page number
     * @var integer $currentPage
     */
    private $currentPage;

    /**
     * This variable stores formatting data
     * @var array $setup
     */
    private $setup;

    /**
     * Avoid redundant pages flag
     * @var boolean $avoidRedundant
     */
    private $avoidRedundant;

    /**
     * Separator between buttons string
     * @var string $separator
     */
    private $separator;

    // --------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setNumItems(0);
        $this->setNumItemsPerPage(1);
        $this->setConfig(array(
            array(
                'type'   => 'first',
                'prefix' => NULL,
                'code'   => '[First(%1$u)]',
                'suffix' => NULL
            ),
            array(
                'type'   => 'previous',
                'prefix' => NULL,
                'code'   => '[Previous(%1$u)]',
                'suffix' => NULL
            ),
            array(
                'type'   => 'far-lower',
                'prefix' => NULL,
                'code'   => '[%1$u]',
                'suffix' => NULL,
                'repeat' => 3
            ),
            array(
                'type'   => 'adjacent-lower',
                'prefix' => NULL,
                'code'   => '{%1$u}',
                'suffix' => NULL,
                'repeat' => 5
            ),
            array(
                'type'   => 'current',
                'prefix' => NULL,
                'code'   => '%1$u',
                'suffix' => NULL
            ),
            array(
                'type'   => 'adjacent-upper',
                'prefix' => NULL,
                'code'   => '{%1$u}',
                'suffix' => NULL,
                'repeat' => 5
            ),
            array(
                'type'   => 'far-upper',
                'prefix' => NULL,
                'code'   => '[%1$u]',
                'suffix' => NULL,
                'repeat' => 3
            ),
            array(
                'type'   => 'next',
                'prefix' => NULL,
                'code'   => '[Next(%1$u)]',
                'repeat' => NULL
            ),
            array(
                'type'   => 'last',
                'prefix' => NULL,
                'code'   => '[Last(%1$u)]',
                'suffix' => NULL
            ),
            array(
                'type'   => 'rowcount',
                'prefix' => NULL,
                'code'   => '[Items: %1$u]',
                'suffix' => NULL
            )
        ));
        $this->setCurrentPage(1);
        $this->setAvoidRedundant(FALSE);
        $this->setSeparator('·');
    }

    // --------------------------------------------------------------------

    /**
     * Set configuration data
     *
     * @param array $config
     */
    public function setConfig($config)
    {
        if (is_array($config) && nwcount($config) > 0) {
            $this->setup = $config;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Generate paginator HTML code (or analogous)
     *
     * @returns string
     */
    public function generate()
    {
        if ($this->getNumPages() == 0) {
            return NULL;
        }
        $paginator = array();
        foreach ($this->setup as $part) {
            switch (a($part, 'type')) {
                case 'first':
                {
                    if ($this->avoidRedundant) {
                        if ($this->getCurrentPage() == 1) {
                            break;
                        }
                    }
                    $paginator[] = a($part, 'preffix') .
                                   sprintf(a($part, 'code'), 1) .
                                   a($part, 'suffix');
                    break;
                }
                case 'last':
                {
                    if ($this->avoidRedundant) {
                        if ($this->getCurrentPage() == $this->getNumPages()) {
                            break;
                        }
                    }
                    $paginator[] = a($part, 'preffix') .
                                   sprintf(a($part, 'code'), $this->getNumPages()) .
                                   a($part, 'suffix');
                    break;
                }
                case 'previous':
                {
                    $page = $this->getCurrentPage() - 1;
                    if ($page < 1) {
                        if ($this->avoidRedundant) {
                            break;
                        }
                        else {
                            $page = 1;
                        }
                    }
                    $paginator[] = a($part, 'preffix') .
                                   sprintf(a($part, 'code'), $page) .
                                   a($part, 'suffix');
                    break;
                }
                case 'next':
                {
                    $page = $this->getCurrentPage() + 1;
                    if ($page > $this->getNumPages()) {
                        if ($this->avoidRedundant) {
                            break;
                        }
                        else {
                            $page = $this->getNumPages();
                        }
                    }
                    $paginator[] = a($part, 'preffix') .
                                   sprintf(a($part, 'code'), $page) .
                                   a($part, 'suffix');
                    break;
                }
                case 'far-lower':
                {
                    // First let's calculate the available index space.
                    // See if there are adjacent elements.
                    $adjacent = NULL;
                    foreach ($this->setup as $e) {
                        if (a($e, 'type') == 'adjacent-lower') {
                            $adjacent = $e;
                            break;
                        }
                    }
                    $start = 1;
                    if ($adjacent) {
                        $end = $this->getCurrentPage() -
                               a($adjacent, 'repeat') - 1;
                    }
                    else {
                        $end = $this->getCurrentPage() - 1;
                    }
                    if ($end - $start < 1) {
                        break;
                    }
                    $repeat = (int)a($part, 'repeat');
                    if ($repeat < 1) {
                        $repeat = 1;
                    }
                    if ($repeat > ($end - $start) / 2) {
                        $repeat = floor(($end - $start) / 2);
                    }
                    $width = floor(($end - $start) / ($repeat + 1));
                    $page  = $start + $width;
                    for ($i = 0; $i < $repeat; $i++) {
                        $paginator[] = a($part, 'preffix') .
                                       sprintf(a($part, 'code'), $page) .
                                       a($part, 'suffix');
                        $page        += $width;
                    }
                    break;
                }
                case 'far-upper':
                {
                    // First let's calculate the available index space.
                    // See if there are adjacent elements.
                    $adjacent = NULL;
                    foreach ($this->setup as $e) {
                        if (a($e, 'type') == 'adjacent-upper') {
                            $adjacent = $e;
                            break;
                        }
                    }
                    $end = $this->getNumPages();
                    if ($adjacent) {
                        $start = $this->getCurrentPage() +
                                 a($adjacent, 'repeat') + 1;
                    }
                    else {
                        $start = $this->getCurrentPage() + 1;
                    }
                    if ($end - $start < 1) {
                        break;
                    }
                    $repeat = (int)a($part, 'repeat');
                    if ($repeat < 1) {
                        $repeat = 1;
                    }
                    if ($repeat > ($end - $start) / 2) {
                        $repeat = floor(($end - $start) / 2);
                    }
                    $width = floor(($end - $start) / ($repeat + 1));
                    $page  = $start + $width;
                    for ($i = 0; $i < $repeat; $i++) {
                        $paginator[] = a($part, 'preffix') .
                                       sprintf(a($part, 'code'), $page) .
                                       a($part, 'suffix');
                        $page        += $width;
                    }
                    break;
                }
                case 'adjacent-lower':
                {
                    $repeat = (int)a($part, 'repeat');
                    if ($repeat < 1) {
                        $repeat = 1;
                    }
                    if ($repeat > $this->getCurrentPage() - 1) {
                        $repeat = $this->getCurrentPage() - 1;
                    }
                    for ($i = $repeat; $i >= 1; $i--) {
                        $page        = $this->getCurrentPage() - $i;
                        $paginator[] = a($part, 'preffix') .
                                       sprintf(a($part, 'code'), $page) .
                                       a($part, 'suffix');
                    }
                    break;
                }
                case 'adjacent-upper':
                {
                    $repeat = (int)a($part, 'repeat');
                    if ($repeat < 1) {
                        $repeat = 1;
                    }
                    if ($repeat > $this->getNumPages() -
                                  $this->getCurrentPage()) {
                        $repeat = $this->getNumPages() -
                                  $this->getCurrentPage();
                    }
                    for ($i = 1; $i <= $repeat; $i++) {
                        $page        = $this->getCurrentPage() + $i;
                        $paginator[] = a($part, 'preffix') .
                                       sprintf(a($part, 'code'), $page) .
                                       a($part, 'suffix');
                    }
                    break;
                }
                case 'current':
                {
                    $paginator[] = a($part, 'preffix') .
                                   sprintf(a($part, 'code'), $this->getCurrentPage(), $this->getNumPages()) .
                                   a($part, 'suffix');
                    break;
                }
                case 'custom':
                {
                    $paginator[] = a($part, 'code');
                    break;
                }
                case 'rowcount':
                {
                    $paginator[] = a($part, 'preffix') .
                                   sprintf(a($part, 'code'), $this->getNumItems()) .
                                   a($part, 'suffix');
                    break;
                }
            }
        }
        return implode($this->getSeparator(), $paginator);
    }
    // --------------------------------------------------------------------

    /**
     * Get computed number pages
     *
     * @return integer
     */
    public function getNumPages()
    {
        return ceil(((int)$this->numItems) / ((int)$this->numItemsPerPage));
    }

    // --------------------------------------------------------------------

    /**
     * Get current page number
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    // --------------------------------------------------------------------

    /**
     * Set current page number
     *
     * @param integer $number
     */
    public function setCurrentPage($number)
    {
        $this->currentPage = (int)$number;
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        }
        else {
            if ($this->currentPage > $this->getNumPages() && $this->getNumPages() > 0) {
                $this->currentPage = $this->getNumPages();
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get current number of items
     *
     * @return integer
     */
    public function getNumItems()
    {
        return $this->numItems;
    }

    // --------------------------------------------------------------------

    /**
     * Set current number of items
     * @param integer $number
     */
    public function setNumItems($number)
    {
        $this->numItems = (int)$number;
        if ($this->numItems < 0) {
            $this->numItems = 0;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get button separator string
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    // --------------------------------------------------------------------

    /**
     * Set button separator string
     *
     * @param string $sep
     */
    public function setSeparator($sep)
    {
        $this->separator = $sep;
    }

    // --------------------------------------------------------------------

    /**
     * Get current number of items per page
     *
     * @return integer
     */
    public function getNumItemsPerPage()
    {
        return $this->numItemsPerPage;
    }
    // --------------------------------------------------------------------

    /**
     * Set number of items per page
     *
     * @param integer $number
     */
    public function setNumItemsPerPage($number)
    {
        $this->numItemsPerPage = (int)$number;
        if ($this->numItemsPerPage < 1) {
            $this->numItemsPerPage = 1;
        }
        else {
            if ($this->numItemsPerPage > $this->numItems &&
                $this->numItems > 0) {
                $this->numItemsPerPage = $this->numItems;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get the offset of the first element of current page
     *
     * @return integer
     */
    public function getOffsetForCurrentPage()
    {
        return $this->numItemsPerPage * ($this->currentPage - 1);
    }

    // --------------------------------------------------------------------

    /**
     * Get the number of items of current page
     *
     * Note that the last page can have less items than the first one.
     *
     * @return integer
     */
    public function numItemsForCurrentPage()
    {
        if ($this->currentPage < $this->getNumPages()) {
            return $this->numItemsPerPage;
        }
        else {
            if (($this->numItems % $this->numItemsPerPage) == 0) {
                return $this->numItemsPerPage;
            }
            else {
                return $this->numItems % $this->numItemsPerPage;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Get configuration data
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->setup;
    }

    // --------------------------------------------------------------------

    /**
     * Get redundant flag value
     *
     * For example, if we are at the first page, the button 'previous' is
     * considered redundant.
     *
     * @return boolean
     */
    public function getAvoidRedundant()
    {
        return $this->avoidRedundant;
    }

    // --------------------------------------------------------------------

    /**
     * Set avoid redundant flag
     *
     * For example, if we are at the first page, the button 'previous' is
     * considered redundant.
     *
     * @param boolean $setting
     */
    public function setAvoidRedundant($setting)
    {
        $this->avoidRedundant = (bool)$setting;
    }

    // --------------------------------------------------------------------
}
