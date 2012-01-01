<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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
   * Provides the tail of a given file. It features two different
   * behaviours for dealing with record breaks:
   *
   * 1. if the $tokStartChar is not provided, it defaults to PHP_EOL,
   *    and a newline is considered a record break. When tailing a file,
   *    which is written from top to bottom, newest lines will thus
   *    appear on top.
   *
   * 2. if your tailed file has atomic records which span multiple
   *    lines, like a log file where each entry is a datetime stamp but
   *    it can span multiple lines, using the simple behaviour will
   *    cause newest records to appear on top yes, but the multiple
   *    lines of a single record will appear switched (newest at the
   *    bottom and oldest on top). In this case you don't want a record
   *    to break on newline, but rather on the datetime markers. So, if
   *    you consider the following log file:
   *
   *    [2011-12-15 22:47:57] [info] [4eea5e7060046] Framework_Process::
   *    run: Executing 'git --git-dir=/www/.cintient/projects/4eb1d5861b
   *    bfb6.53900422/sources/.git ls-remote' [CALLER=Git.php] [LINE=105
   *    ]
   *    [2011-12-15 22:47:57] [info] [4eea5e7060046] runBuildWorker:
   *    Starting project build. [PID=7] [CALLER=N/A] [LINE=N/A]
   *
   *    You would want to have the following params set, in order to
   *    have multiple lines displaying correctly:
   *    . $tokStartChar:  '['
   *    . $tokEndChar:    ']'
   *    . $tokMatchRegex: '^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]$'
   *
   * @param string $file The file to access
   * @param string $nol The number of "lines" to access
   * @param string $tokStartChar The token start char. If this is not
   * specified, it defaults to PHP_EOL and makes tail() assume the
   * simple newline breaking behaviour.
   * @param string $tokEndChar The token end char
   * @param string $tokMatchRegex The token matching regex
   * @return boolean|string False if anything goes wrong, the tail
   * string if successful.
   */
  static public function tail($file, $nol = 10, $tokStartChar = PHP_EOL, $tokEndChar = null, $tokMatchRegex = null)
  {
    if (!is_readable($file)) {
      return false;
    }
    if (!($fd = @fopen($file, 'r'))) {
      return false;
    }
    $lines = '';
    $line = '';
    $tokenChars = '';
    $last2Chars = '';
    for ($i = 0, $fpos = -1; fseek($fd, $fpos, SEEK_END) !== -1 && $i < $nol; $fpos--) {
      $char = fgetc($fd);
      $line .= $char;

      if (!is_null($tokEndChar) && $tokEndChar !== '') {
        if ($char == $tokEndChar) { // Start accumulating the token chars
          $tokenChars = $char;
          continue;
        }
        if (!empty($tokenChars)) { // Keep accumulating the token chars until the start token char is found
          $tokenChars .= $char;
        }
        if ($char == $tokStartChar) { // Arrived at the end of the token, this could be our "newline"
          $tokenChars = strrev($tokenChars);
          if (preg_match('/'.$tokMatchRegex.'/', $tokenChars)) {
            $lines .= strrev($line);
            $line = '';
            $i++;
          }
          $tokenChars = '';
        }
      } else {
        $last2Chars .= $char;
        // The $last2Chars logic deals with the possibility that there
        // are systems where a newline is actually 2 chars long, such
        // as "\n\r" or "\r\n".
        //TODO: testcase this properly
        $last2Chars = substr($last2Chars, -2);
        if ($char == PHP_EOL || $last2Chars == PHP_EOL) {
          $lines .= strrev($line);
          $line = '';
          $i++;
        }
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