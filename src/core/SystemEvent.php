<?php
/*
 * 
 * Cintient, Continuous Integration made simple.
 * Copyright (c) 2011, Pedro Mata-Mouros Fonseca
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * . Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * . Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 *   
 * . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
 *   the names of its contributors may be used to endorse or promote
 *   products derived from this software without specific prior
 *   written permission.
 *   
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 */

require 'lib/Log-1.12.3/Log.php';

/**
 * 
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