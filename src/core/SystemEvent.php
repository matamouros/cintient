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

require 'lib/Log-1.12.3/Log.php';

/**
 * @package System
 */
class SystemEvent extends Log
{
  /**
   * These consts are cleverly rigged to directly map to the existing PEAR::Log
   * event raising methods.
   */
  const DEBUG     = 'debug';
  const INFO      = 'info';
  const NOTICE    = 'notice';
  const WARNING   = 'warning';
  const ERROR     = 'err';
  const CRITICAL  = 'crit';
  const ALERT     = 'alert';
  const EMERGENCY = 'emerg';

  /**
   *
   */
  static private function _singleton()
  {
    static $instance;
    if (!isset($instance) || !($instance instanceof SystemEvent)) {
      $options = array( 'append'     => true,
                        'locking'    => false,
                        'mode'       => 0640,
                        'timeFormat' => '[%Y-%m-%d %H:%M:%S]',
                        'lineFormat' => '%1$s [%3$s] %4$s',
                 );
      $instance = parent::singleton('file', LOG_FILE, 'project-ci', $options, PEAR_LOG_DEBUG);
    }
    return $instance;
  }

  static private function _getBacktraceInfo()
  {
    $bt = debug_backtrace();
    return (' [CALLER=' . (isset($bt[2]['file'])?basename($bt[2]['file']):'N/A') . '] [LINE=' . (isset($bt[2]['line'])?(string)$bt[2]['line']:'N/A') . ']');
  }

  /**
   *
   * @param unknown_type $severity
   * @param string $msg
   * @param string $location
   */
  public static function raise($severity, $msg, $location = null)
  {
    $instance = self::_singleton();
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
      $severity = self::INFO;
    }
    $instance->$severity((empty($location)?'':$location.': ') . $msg . self::_getBacktraceInfo());
  }
}