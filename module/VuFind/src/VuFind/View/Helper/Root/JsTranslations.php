<?php
/**
 * JsTranslations helper for passing translation text to Javascript
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace VuFind\View\Helper\Root;

use Laminas\View\Helper\AbstractHelper;

/**
 * JsTranslations helper for passing translation text to Javascript
 *
 * @category VuFind
 * @package  View_Helpers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class JsTranslations extends AbstractHelper
{
    /**
     * Translate helper
     *
     * @var Translate
     */
    protected $translate;

    /**
     * Translate + escape helper
     *
     * @var TransEsc
     */
    protected $transEsc;

    /**
     * Variable name to store translations
     *
     * @var string
     */
    protected $varName;

    /**
     * Strings to translate (key = js key, value = string to translate)
     *
     * @var array
     */
    protected $strings = [];

    /**
     * Constructor
     *
     * @param Translate $translate Translate helper
     * @param TransEsc  $transEsc  Translate + escape helper
     * @param string    $varName   Variable name to store translations
     */
    public function __construct(
        Translate $translate,
        TransEsc $transEsc,
        $varName = 'vufindString'
    ) {
        $this->translate = $translate;
        $this->transEsc = $transEsc;
        $this->varName = $varName;
    }

    /**
     * Add strings to the internal array.
     *
     * @param array $new Strings to add
     *
     * @return void
     */
    public function addStrings($new)
    {
        foreach ($new as $k => $v) {
            $this->strings[$k] = $v;
        }
    }

    /**
     * Generate JSON from the internal strings
     *
     * @return string
     */
    public function getJSON()
    {
        return $this->getJSONFromArray($this->strings);
    }

    /**
     * Generate JSON from an array
     *
     * @param array $strings Strings to translate (key = js key, value = string to
     * translate)
     *
     * @return string
     */
    public function getJSONFromArray(array $strings): string
    {
        foreach ($strings as $key => &$translation) {
            $translateFunc
                = substr($key, -5) === '_html' || substr($key, -10) === '_unescaped'
                ? $this->translate : $this->transEsc;
            // $translation could be a string or an array of parameters; this code
            // normalizes it into a parameter list for the translator.
            $translation = ($translateFunc)(...((array)$translation));
        }
        return json_encode($strings);
    }

    /**
     * Assign JSON to a variable.
     *
     * @return string
     */
    public function getScript()
    {
        return $this->varName . ' = ' . $this->getJSON() . ';';
    }
}
