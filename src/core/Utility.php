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