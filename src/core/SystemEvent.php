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
  const TRACE     = 4096;
  const DEBUG     = 2048;
  const INFO      = 1024;
  const NOTICE    = 512;
  const WARNING   = 256;
  const ERROR     = 128;
  const CRITICAL  = 64;
  const ALERT     = 32;
  const EMERGENCY = 16;

  /**
   * The string placeholder which will get replaced for the provided vars.
   * WARNING: this placeholder must be regexp special chars escaped!
   */
  const STRING_PLACEHOLDER = '\{\}';

  static $mapConstToDesc = array (
    self::TRACE     => 'trace',
    self::DEBUG     => 'debug',
    self::INFO      => 'info',
    self::NOTICE    => 'notice',
    self::WARNING   => 'warn',
    self::ERROR     => 'err',
    self::CRITICAL  => 'crit',
    self::ALERT     => 'alert',
    self::EMERGENCY => 'emerg',
  );

  static $mapDescToConst = array (
    'trace'   => self::TRACE,
    'debug'   => self::DEBUG,
    'info'    => self::INFO,
    'notice'  => self::NOTICE,
    'warn'    => self::WARNING,
    'err'     => self::ERROR,
    'crit'    => self::CRITICAL,
    'alert'   => self::ALERT,
    'emerg'   => self::EMERGENCY,
  );

  static $severityLevel = self::INFO;

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
                        //'timeFormat' => '[%Y-%m-%d %H:%M:%S]',
                        'timeFormat' => '[%Y-%m-%d %H:%M:%S', // An ending ']' is missing on purpose, because of the hack done on PEAR::Log (file.php), in order to have milliseconds on logs
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

  /**
   * The actual logging method. You can call this directly, or you could access
   * the more user-friendly magical methods, the latter being the recommended.
   * @see __callStatic
   * 
   * @param int $severity The severity level to log the message with.
   * @param string | array $args Either the string with the message or an array
   *        with the message on the first position and values to replace
   *        placeholders within the message string. Placeholders are defined by
   *        self::STRING_PLACEHOLDER.
   * @param @deprecated string | null $location The location where the call
   *        occurred. It is now deprecated because we infer this automagically
   *        through debug_backtrace(). Although it comes a bit more expensive,
   *        it's preferable in terms of readability and maintainability.
   */
  public static function raise($severity, $args, $location = null)
  {
    if (self::$severityLevel < $severity) {
      return false;
    }
    $instance = self::_singleton();

    //
    // Access class and method where the log happened automagically, instead of
    // having the caller have to provide this everytime. Do this with a pretty
    // "Class::method: " format on the message string.
    //
    $bt = debug_backtrace();
    $class = '';
    $method = '';
    if (!empty($bt[0]['function'])) {
      $function = $bt[0]['function'] . ': ';
    }
    if (!empty($bt[0]['class'])) {
      $class = $bt[0]['class'] . '::';
    }

    //
    // Logic to substitute all placeholders found in the message string with
    // their appropriate values.
    // 
    if (!is_array($args)) {
      $msg = $args; // No placeholders assumed
    } else {
      $i = 0;
      $msg = array_shift($args); // Remove the message from the first position
      $msg = preg_replace_callback( // And now for a bit of black magic...
        '/(%)?' . self::STRING_PLACEHOLDER . '(%)?/',
        function ($m) use (&$i, &$args) {
          $r = $args[$i];
          $i++;
          return $r;
        },
        $msg,
        -1
      );
    }

    //
    // Backtrace stuff, for debug levels only
    //
    $btInfo = '';
    if (self::$severityLevel >= self::DEBUG) {
      $btInfo = (' [CALLER=' . (isset($bt[2]['file'])?basename($bt[2]['file']):'N/A') . '] [LINE=' . (isset($bt[2]['line'])?(string)$bt[2]['line']:'N/A') . ']');
    }

    $method = self::$mapConstToDesc[$severity];
    $instance->$method($class . $function . $msg . $btInfo);
  }

  /**
   * Magic method implementation for calling unexisting static methods.
   *
   * @param $name The name of the log method being accessed. Also corresponds to
   *        the severity level of the message being logged.
   * @param $args Arguments passed in by the magic __callStatic() call
   *
   * @return mixed
   */
  public static function __callStatic($name, $args)
  {
    if (empty($mapDescToConst[strtolower($name)])) {
      trigger_error("No valid method available for calling", E_USER_ERROR);
    }
    self::raise($mapDescToConst[$name], $args);
  }
}