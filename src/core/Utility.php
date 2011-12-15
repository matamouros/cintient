<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010, 2011, Pedro Mata-Mouros Fonseca
 *
 *  This file is part of Cintient.
 *
 *  Cintient is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Cintient is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Cintient. If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 *
 * @package     Utility
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Utility
{
  /**
   * Transforms a textual php.ini representation of size (kilobytes,
   * megabytes, gigabytes) into bytes.
   *
   * @param string $str
   */
  static public function phpIniSizeToBytes($str)
  {
    $val = trim($str);
    $last = strtolower($str[strlen($str)-1]);
    switch($last) {
      case 'g': $val *= 1024;
      case 'm': $val *= 1024;
      case 'k': $val *= 1024;
    }
    return $val;
  }

  static public function bytesToHumanReadable($size)
  {
    if ($size < 1024.0) {
      $size = $size . ' B';
    } elseif ($size < 1048576.0) {
      $size = round($size/1024.0, 2) . 'KB';
    } elseif ($size < 1073741824.0) {
      $size = round($size/1048576.0, 2) . 'MB';
    } elseif ($size < 1099511627776.0) {
      $size = round($size/1073741824.0, 2) . 'GB';
    } else {
      $size = round($size/1099511627776.0, 2) . 'TB';
    }
    return $size;
  }

  /**
   * This provides $universe^$size, e.g., 61^4 = 13,845,841
   */
  static public function generateRandomString($size = 4, $universe = 'abcdefghijklmnopqrstuvwzyzABCDEFGHIJKLMNOPQRSTUVWZYZ0123456789')
  {
    $uid = '';
    for ($i = 0; $i < $size; $i++) {
      $uid .= substr($universe, rand(0, strlen($universe)), 1);
    }
    return $uid;
  }

  /**
   * Provides the tail of a given file.
   *
   * @param string $file
   * @param string $nol The number of lines to access
   * @return boolean|string False if anything goes wrong, the string if
   * successful.
   */
  static public function tail($file, $nol = 10)
  {
    if (!is_readable($file)) {
      return false;
    }
    if (!($fd = @fopen($file, 'r'))) {
      return false;
    }
    $lines = '';
    $line = '';
    $last2Chars = '';
    for ($i = 0, $fpos = -1; fseek($fd, $fpos, SEEK_END) !== -1 && $i < $nol; $fpos--) {
      $char = fgetc($fd);
      $line .= $char;
      $last2Chars .= $char;
      $last2Chars = substr($last2Chars, -2);
      if ($char == PHP_EOL || $last2Chars == PHP_EOL) {
        $lines .= strrev($line);
        $line = '';
        $i++;
      }
    }
    @fclose($fd);
    return trim($lines);

  }

  /**
   * A function for making time periods readable
   *
   * @author      Aidan Lister <aidan@php.net>
   * @version     2.0.1
   * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
   * @param       int     number of seconds elapsed
   * @param       string  which time periods to display
   * @param       bool    whether to show zero time periods
   */
  static public function timeDurationToHumanReadable($seconds, $use = null, $zeros = false)
  {
    // Define time periods
    $periods = array (
          'years'     => 31556926,
          'Months'    => 2629743,
          'weeks'     => 604800,
          'days'      => 86400,
          'hours'     => 3600,
          'minutes'   => 60,
          'seconds'   => 1
    );

    // Break into periods
    $seconds = (float) $seconds;
    $segments = array();
    foreach ($periods as $period => $value) {
      if ($use && strpos($use, $period[0]) === false) {
        continue;
      }
      $count = floor($seconds / $value);
      if ($count == 0 && !$zeros) {
        continue;
      }
      $segments[strtolower($period)] = $count;
      $seconds = $seconds % $value;
    }

    // Build the string
    $string = array();
    foreach ($segments as $key => $value) {
      $segment_name = substr($key, 0, -1);
      $segment = $value . ' ' . $segment_name;
      if ($value != 1) {
        $segment .= 's';
      }
      $string[] = $segment;
    }

    return implode(', ', $string);
  }
}