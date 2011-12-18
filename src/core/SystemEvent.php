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

require 'lib/PEAR/Log.php';

/**
 * Class for handling logging.
 *
 * @package     Framework
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class SystemEvent extends Log
{
  /**
   * These consts are rigged to directly map to the existing PEAR::Log
   * event raising methods.
   */
  const DEBUG     = 2048;
  const INFO      = 1024;
  const NOTICE    = 512;
  const WARNING   = 256;
  const ERROR     = 128;
  const CRITICAL  = 64;
  const ALERT     = 32;
  const EMERGENCY = 16;

  static $map = array(
    self::DEBUG     => 'debug',
    self::INFO      => 'info',
    self::NOTICE    => 'notice',
    self::WARNING   => 'warning',
    self::ERROR     => 'err',
    self::CRITICAL  => 'crit',
    self::ALERT     => 'alert',
    self::EMERGENCY => 'emerg',
  );

  static $severityLevel = self::DEBUG;

  /**
   *
   */
  static private function _singleton()
  {
    static $instance;
    if (!isset($instance) || !($instance instanceof Log)) {
      $options = array( 'append'     => true,
                        'locking'    => false,
                        'mode'       => 0640,
                        'timeFormat' => '[%Y-%m-%d %H:%M:%S]',
                        'lineFormat' => '%1$s [%3$s] [' . uniqid() . '] %4$s',
                 );
      $instance = parent::singleton('file', CINTIENT_LOG_FILE, 'cintient', $options, PEAR_LOG_DEBUG);
    }
    return $instance;
  }

  static public function setSeverityLevel($severityLevel = self::DEBUG)
  {
    self::$severityLevel = (int)$severityLevel;
  }

  static private function _getBacktraceInfo()
  {
    $ret = '';
    if (self::$severityLevel == self::DEBUG) {
      $bt = debug_backtrace();
      $ret = (' [CALLER=' . (isset($bt[2]['file'])?basename($bt[2]['file']):'N/A') . '] [LINE=' . (isset($bt[2]['line'])?(string)$bt[2]['line']:'N/A') . ']');
    }
    return $ret;
  }

  /**
   *
   * @param unknown_type $severity
   * @param string $msg
   * @param string $location
   */
  public static function raise($severity, $msg, $location = null)
  {
    if (self::$severityLevel < $severity) {
      return false;
    }
    $instance = self::_singleton();
    /*
    if ($severity == self::EMERGENCY) {
      //
      // TODO: an email is probably pretty much appropriate...
      //
    } elseif ($severity == self::ALERT) {
    } elseif ($severity == self::CRITICAL) {
    } elseif ($severity == self::ERROR) {
    } elseif ($severity == self::WARNING) {
    } elseif ($severity == self::NOTICE) {
    } elseif ($severity == self::INFO) {
    } elseif ($severity == self::DEBUG) {
    } else {
      //$severity = self::INFO;
    }*/
    $method = self::$map[$severity];
    $instance->$method((empty($location)?'':$location.': ') . $msg . self::_getBacktraceInfo());
  }
}