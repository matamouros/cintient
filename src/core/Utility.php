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
 * @package Utility
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
}