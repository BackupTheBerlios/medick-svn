<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 - 2007 Aurelian Oancea < aurelian [ at ] locknet [ dot ] ro >
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice,
//   this list of conditions and the following disclaimer.
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
//   * Neither the name of Aurelian Oancea nor the names of his contributors may
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
//////////////////////////////////////////////////////////////////////////////////
// }}}

/**
 *
 * @package medick.action.view
 * @subpackage Json
 * @author Aurelian Oancea
 */
class JSON extends Object {

  public static function encode($value) {
      if(function_exists('json_encode')) return json_encode($value);
      if (is_array($value)) return JSON::array_encode($value);
      else return JSON::scalar_encode($value);
  }
  
  public static function decode($value) {
      if(function_exists('json_decode')) return json_decode($value);
      throw new MedickException('JSON::decode(string $value) method is not implemented.');
  }

  public static function array_encode(Array $value) {
      $tmp= array();

      if ( array_keys($value) !== range(0, sizeof($value) - 1) ) {
          foreach ($value as $k=>$v) {
              $tmp[]= JSON::string_encode((string)$k) . ' : ' . JSON::encode($v);
          }
          return '{' . join(', ', $tmp) . '}';
      }

      for ($i= 0; $i < sizeof($value); $i++) {
          $tmp[]= JSON::encode($value[$i]);
      }
      return '['. join(', ', $tmp) . ']';
  }

  public static function scalar_encode($value) {
      if     (is_numeric($value)) return (string)$value;
      elseif (is_string($value))  return JSON::string_encode($value);
      elseif (is_bool($value))    return $value ? 'true' : 'false';
      elseif (is_null($value))    return 'null';
      else throw new MedickException($value . ' is not a scalar!');
  }

  public static function string_encode($string) {
      $search  = array('\\', "\n", "\t", "\r", "\b", "\f", '"');
      $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
      $string  = str_replace($search, $replace, $string);
      $string = str_replace(array(chr(0x08), chr(0x0C)), array('\b', '\f'), $string);
      return '"' . $string . '"';
  }

}

