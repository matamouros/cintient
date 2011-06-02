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
 * @author pfonseca
 * @package System
 */
class Process
{
  private $_cmd;

  function __construct($cmd)
  {
    if (empty($cmd)) {
      return false;
    }
    $this->_cmd = $cmd;
  }

  public function isRunning()
  {
    if (PHP_OS == 'Windows') {
      //(@pclose(popen("start /B ". $this->_cmd, "r"));
    } else {
      $output = array();
      $ret = 1;
      $cmd = 'ps ax | grep "' . $this->_cmd . '";';
      @exec($cmd, $output, $ret);
      if ($ret !== 0) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not query system for running process. [PROCESS={$this->_cmd}]", __METHOD__);
        return true; // Seriously, don't assume it's not running. Better that the user fixes this first.
      }
      if (isset($output[0]) && !preg_match('/( ps ax \| | grep )/', $output[0])) {
        return true;
      }
    }
    return false;
  }

  public function runAsync()
  {
    //
    // Execute the cron (background run)
    // @see http://pt.php.net/manual/en/function.exec.php#86329
    //
    if (PHP_OS == 'Windows') {
      @pclose(popen("start /B ". $this->_cmd, "r"));
    } else {
      @exec($this->_cmd . ' > /dev/null 2>&1 &');
    }
    return true;
  }
}