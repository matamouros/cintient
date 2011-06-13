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
 * Utility class for handling external programs' execution.
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

/**
 * Handles all command line executions, taking into account the host
 * system.
 */
class Framework_Process
{
  private $_executable;
  private $_args;

  const SILENT = 0;
  const STDOUT = 1; // just stdout
  const STDERR = 3; // stdout + stderr

  public function __construct($filename, Array $args = array())
  {
    $this->setExecutable($filename);
    $this->setArgs($args);
  }

  public function addArg($key, $value = null)
  {
    $this->_args[] = array($key, $this->_quoteArgument($value));
  }

  public function getArgs()
  {
    $args = '';
    foreach ($this->_args as $arg) {
      $key = array_shift($arg);
      $value = array_shift($arg);
      $args .=  $key . (!empty($value)?' ' . $value:'');
    }
    return $args;
  }

  public function getCommand()
  {
    return $this->getExecutable() . ($this->getArgs()?' ' . $this->getArgs():'');
  }

  public function getExecutable()
  {
    return $this->_executable;
  }

  public function setArgs(Array $args = array())
  {
    $this->_args = array();
    foreach ($args as $arg) {
      $this->_args[] = array(array_shift($arg), $this->_quoteArgument(array_shift($arg)));
    }
  }

  public function setExecutable($filename)
  {
    // TODO: We should be able to use is_executable, but it's not working
    // for relative executables in the PATH
    /*
    if (!is_executable($filename)) {
      SystemEvent::raise(SystemEvent::ERROR, "Invalid executable specified. [FILENAME={$filename}]", __METHOD__);
      return false;
    }*/
    $this->_executable = escapeshellcmd($filename);
  }

  public function isRunning()
  {
    if (Framework_HostOs::isWindows()) {
      //(@pclose(popen("start /B ". $this->_executable, "r"));
    } else {
      $output = array();
      $ret = 1;
      $cmd = 'ps ax | grep "' . $this->getExecutable() . '";';
      @exec($cmd, $output, $ret);
      if ($ret !== 0) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not query system for running executable. [EXECUTABLE={$this->getExecutable()}]", __METHOD__);
        return true; // Seriously, don't assume it's not running. Better that the user fixes this first.
      }
      if (isset($output[0]) && !preg_match('/( ps ax \| | grep )/', $output[0])) {
        return true;
      }
    }
    return false;
  }

  /**
   * Executes a provided executable and given arguments.
   * @see http://pt.php.net/manual/en/function.exec.php#86329
   *
   * @todo This is very likely broken in a number of cases, Windows,
   * with/without background running, error supression, etc.
   *
   * @param bool $runInBackground Leave the command running in the
   * background and continue execution
   * @param integer $output Output supression: completely silent execution,
   * just stdout or full output (stdout and stderr)
   *
   * @return Array An array with the lastline of output, the full output,
   * and the return code from the execution of the command
   */
  public function run($runInBackground = false, $output = self::SILENT)
  {
    if (Framework_HostOs::isWindows()) {
      @pclose(@popen("start /B ". $this->getCommand(), "r"));
    } else {
      $outputSupression = '';
      switch ($output) {
        // Just output stdout
        case self::STDOUT:
          $outputSupression = ' 2>/dev/null';
          break;
        // Full stderr and stdout output
        // TODO: This will probably cause PHP to hang in case of running
        // in the background. In the exec() manual section, they state
        // that in these cases all ouput must be redirected to a filename
        // or other stream, or else PHP will be left hanging... Check this
        // later.
        case self::STDERR:
          $outputSupression = ' 2>&1';
          break;
        // Fully silent
        case self::SILENT:
        default:
      	  $outputSupression = ' > /dev/null 2>&1';
          break;
      }
      $fullOuput = array();
      $ret = 1;
      $lastline = null;
      $command = $this->getCommand() . $outputSupression . ($runInBackground?' &':'');
      SystemEvent::raise(SystemEvent::INFO, "Executing '$command'", __METHOD__);
      $lastline = @exec($command, $fullOutput, $ret);
      return array($lastline, $fullOutput, $ret);
    }
    return true;
  }

	/**
   * Put quotes around the given String if necessary.
   *
   * <p>If the argument doesn't include spaces or quotes, return it
   * as is. If it contains double quotes, use single quotes - else
   * surround the argument by double quotes.</p>
   *
   * @exception BuildException if the argument contains both, single
   *                           and double quotes.
   */
  private function _quoteArgument($argument)
  {
    if (strpos($argument, '"') !== false && strpos($argument, "'") !== false) {
      //throw new BuildException("Can't handle single and double quotes in same argument");
      // TODO: throw our own exception here
      return false;
    } elseif (strpos($argument, '"') !== false || strpos($argument, "'") !== false || strpos($argument, " ") !== false) {
      return escapeshellarg($argument);
    } else {
      return $argument;
    }
  }
}