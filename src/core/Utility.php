<?php


/**
 * 
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
}