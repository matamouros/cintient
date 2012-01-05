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
class Framework_Process extends Framework_BaseObject
{
  protected $_executable;
  protected $_args;
  protected $_returnValue;
  protected $_stdin;
  protected $_stdout;
  protected $_stderr;
  protected $_stdoutCallbacks;
  protected $_stderrCallbacks;

  public function __construct($exec = null, Array $args = array())
  {
    $this->setExecutable($exec);
    $this->setArgs($args);
    $this->_returnValue = null;
    $this->_stdin = null;
    $this->_stdout = null;
    $this->_stderr = null;
    $this->_stdoutCallbacks = array();
    $this->_stderrCallbacks = array();
  }

  public function addArg($key, $value = null)
  {
    $this->_args[] = array($key, $this->_quoteArgument($value));
  }

  public function appendToStderr($txt)
  {
    $this->_stderr .= $txt;
  }

  public function appendToStdout($txt)
  {
    $this->_stdout .= $txt;
  }

  public function getArgs()
  {
    $args = '';
    foreach ($this->_args as $arg) {
      $key = array_shift($arg);
      $value = array_shift($arg);
      $args .= $key . (!empty($value) ? ' ' . $value : '');
    }
    return $args;
  }

  public function getCmd()
  {
    return $this->getExecutable() . ($this->getArgs() ? ' ' . $this->getArgs() : '');
  }

  public function setArgs(Array $args = array())
  {
    $this->_args = array();
    foreach ($args as $arg) {
      $this->_args[] = array(array_shift($arg), $this->_quoteArgument(array_shift($arg)));
    }
  }

  public function setExecutable($filename, $escapeShellCmd = true)
  {
    // TODO: We should be able to use is_executable, but it's not working
    // for relative executables in the PATH
    /*
    if (!is_executable($filename)) {
      SystemEvent::raise(SystemEvent::ERROR, "Invalid executable specified. [FILENAME={$filename}]", __METHOD__);
      return false;
    }*/
    if ($escapeShellCmd) {
      $filename = escapeshellcmd($filename);
    }
    $this->_executable = $filename;
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

  public function registerStdoutCallback($callback)
  {
    //$this->_stdoutCallbacks[$]
  }

  /**
   * Executes a provided executable and given arguments.
   * @see http://www.php.net/manual/en/function.proc-open.php#95207
   *
   * @todo This is very likely broken in a number of cases, Windows,
   * with/without background running, error supression, etc.
   *
   * @param bool $inBg Leave the command running in the background and
   * continue execution
   * @param Array $pipes
   * @param callback $cb
   * @param integer $output Output supression: completely silent execution,
   * just stdout or full output (stdout and stderr)
   *
   * @return Array An array with the lastline of output, the full output,
   * and the return code from the execution of the command
   */
  public function run($inBg = false)
  {
    // Get windows run-in-background out of the way
    if (Framework_HostOs::isWindows() && $inBg) {
      SystemEvent::raise(SystemEvent::INFO, "Executing '{$this->getCmd()}'", __METHOD__);
      return (bool)(@pclose(@popen("start /B " . $this->getCmd(), "r")) !== -1);
    }

    $descriptorSpec = array(
      0 => array("pipe", "r"), # STDIN
      1 => array("pipe", "w"), # STDOUT
      2 => array("pipe", "w"), # STDERR
    );

    $cmd = $this->getCmd() . ($inBg ? ' &' : '');
    SystemEvent::raise(SystemEvent::INFO, "Executing '{$cmd}'", __METHOD__);
    $ptr = proc_open($cmd, $descriptorSpec, $pipes, null);
    if (!is_resource($ptr)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems executing external process. [CMD={$cmd}]", __METHOD__);
      return false;
    }
    if ($inBg) {
      return true; // Apparently no pipes need closing...
    }

    // Feed stdin to the child process
    if (!empty($this->_stdin)) {
      // TODO: chunks at a time, not the whole damn thing at once!
      fwrite($pipes[0], $this->_stdin);
      fclose($pipes[0]);
    }

    $first_exitcode = null;

    while (($buffer = fgets($pipes[1], 1024)) != null ||
           ($errbuf = fgets($pipes[2], 1024)) != null)
    {
      if (!isset($flag)) {
        $pstatus = proc_get_status($ptr);
        if (!$pstatus['running']) {
          $first_exitcode = $pstatus["exitcode"];
          $flag = true;
        }
      }
      if (strlen($buffer)) {
        $this->appendToStdout($buffer);
        //TODO: call registered callbacks
      } elseif (strlen($errbuf)) {
        $this->appendToStderr($errbuf);
        //TODO: call registered callbacks
      }
      $buffer = 0;
      $errbuf = 0;
    }

    @fclose($pipes[1]);
    @fclose($pipes[2]);

    // Get the expected *exit* code to return the value
    $pstatus = proc_get_status($ptr);
    if (!strlen($pstatus["exitcode"]) || $pstatus["running"]) {
      // we can trust the retval of proc_close()
      if ($pstatus["running"]) {
        proc_terminate($ptr);
      }
      $ret = proc_close($ptr);
    } else {
      if ((($first_exitcode + 256) % 256) == 255 &&
          (($pstatus["exitcode"] + 256) % 256) != 255) {
        $ret = $pstatus["exitcode"];
      } elseif (!strlen($first_exitcode)) {
        $ret = $pstatus["exitcode"];
      } elseif ((($first_exitcode + 256) % 256) != 255) {
        $ret = $first_exitcode;
      } else {
        $ret = 0; // we "deduce" an EXIT_SUCCESS ;)
      }
      proc_close($ptr);
    }

    $this->setReturnValue(($ret + 256) % 256);
    return $this->getReturnValue();
  }

  /**
   * Run an external command asynchronously, i.e., don't wait for it to
   * finish.
   */
  public function runInBackground($output = self::STDOUT)
  {
    if (Framework_HostOs::isWindows()) {
      @pclose(@popen("start /B " . $this->getCmd(), "r"));
    } else {
      $outputSupression = '';
      switch ($output) {

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
      	  $outputSupression = ' > /dev/null 2>&1';
          break;
        // Just output stdout
        case self::STDOUT:
        default:
          $outputSupression = ' 2>/dev/null';
          break;
      }
      $fullOuput = array();
      $ret = 1;
      $lastline = null;
      $command = $this->getCmd() . $outputSupression . ' &';
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