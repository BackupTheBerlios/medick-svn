<?php
// {{{ License
// ///////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice, 
//   this list of conditions and the following disclaimer. 
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation 
//   and/or other materials provided with the distribution. 
//   * Neither the name of locknet.ro nor the names of its contributors may 
//   be used to endorse or promote products derived from this software without 
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
// FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
// CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
// OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
// 
// $Id$
// 
// ///////////////////////////////////////////////////////////////////////////////
// }}}

/** 
 * @package locknet7.active.record.support
 * Based on: http://dev.rubyonrails.com/file/trunk/activesupport/lib/active_support/inflections.rb
 */
 
class Inflector extends Object {

    /** 
     * Transform word from singular to plural
     * @param string word, the word we want to pluralize
     */
    public static function pluralize($word) {
        $rules = array(
            '/(quiz)$/i'               => '\1zes', 
            '/^(ox)$/i'                => '\1en',
            '/([m|l])ouse$/i'          => '\1ice',
            '/(matr|vert|ind)ix|ex$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i'         => '\1es',
            '/([^aeiouy]|qu)ies$/i'    => '\1y',
            '/([^aeiouy]|qu)y$/i'      => '\1ies',
            '/(hive)$/i'               => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i'                  => 'ses',
            '/([ti])um$/i'             => '\1a',
            '/(buffal|tomat)o$/i'      => '\1oes',
            '/(bu)s$/i'                => '\1ses',
            '/(alias|status)/i'        => '\1es',
            '/(octop|vir)us$/i'        => '\1i',
            '/(ax|test)is$/i'          => '\1es',
            '/s$/i'                    => 's',
            '/$/'                      => 's'
        );
        
        foreach ($rules AS $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }
        return $word;
    }

    /** 
     * Transform word from plural to singular
     * @param string word, the word we want to singularize
     */
    public static function singularize($word) {
        $rules = array(
            '/s$/i'                 => '',
            '/(n)ews$/i'            => '\1ews',
            '/([ti])a$/i'           => '\1um',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/(^analy)ses$/i'       => '\1sis',
            '/([^f])ves$/i'         => '\1fe',
            '/(hive)s$/i'           => '\1',
            '/(tive)s$/i'           => '\1',
            '/([lr])ves$/i'         => '\1f',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/(s)eries$/i'          => '\1eries',
            '/(m)ovies$/i'          => '\1ovie',
            '/(x|ch|ss|sh)es$/i'    => '\1',
            '/([m|l])ice$/i'        => '\1ouse',
            '/(bus)es$/i'           => '\1',
            '/(o)es$/i'             => '\1',
            '/(shoe)s$/i'           => '\1',
            '/(cris|ax|test)es$/i'  => '\1is',
            '/([octop|vir])i$/i'    => '\1us',
            '/(alias|status)es$/i'  => '\1',
            '/^(ox)en/i'            => '\1',
            '/(vert|ind)ices$/i'    => '\1ex',
            '/(matr)ices$/i'        => '\1ix',
            '/(quiz)zes$/i'         => '\1'
        );
        
        foreach (array_reverse($rules) as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }
        return $word;
    }
}

