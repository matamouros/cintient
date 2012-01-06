<?php

class Framework_WinProcess
{

  public static function isRunning()
  {
    $result = false;
    $processInfo = self::readPIDFile();

    if (is_array($processInfo) && isset($processInfo['pid'])) {
      if (self::isPIDRunning($processInfo['pid'])) {
        $result = true;
      }
    }
    return $result;
  }

  public static function workersDir()
  {
    $result = false;
    if (Framework_HostOs::isWindows()) {
      $dir = dirname(dirname(dirname(__FILE__))) . '/workers';
      $dir = str_replace('//', '/', str_replace('\\', '/', $dir));
      if (is_dir($dir) && is_writable($dir)) {
        $result = $dir;
      } else {
        SystemEvent::raise(SystemEvent::ERROR, " The workers dir is not writable!", __METHOD__);
      }
    }
    return $result;
  }

  public static function getPIDFile()
  {
    $result = false;
    if (Framework_HostOs::isWindows()) {
      $dir = self::workersDir();
      if ($dir) {
        $result = $dir . '/' . strtolower(str_replace('.php', '.pid', basename(__FILE__)));
      }
    }
    return $result;
  }

  public static function readPIDFile()
  {
    $result = false;
    if (Framework_HostOs::isWindows()) {
      $pidFile = self::getPIDFile();
      if ($pidFile && is_readable($pidFile)) {
        list($pid, $_time) = explode('|', trim(file_get_contents($pidFile)));
        list($H, $i, $s, $m, $d, $Y) = explode('.', $_time);
        $time = mktime($H, $i, $s, $m, $d, $Y);
        $result = array('pid' => $pid, 'time' => $time);
      }
    }
    return $result;
  }

  public static function isPIDRunning($pid)
  {
    $pid = (int) sprintf('%d', $pid);
    $cmd = "wmic PROCESS where (ProcessId=$pid) get ProcessId /VALUE | grep \"ProcessId=$pid\" -c";
    $result = ($pid > 0) ? (bool) sprintf('%d', shell_exec($cmd)) : false;
    return $result;
  }

  public static function refreshPIDFile()
  {
    $result = false;
    if (Framework_HostOs::isWindows()) {
      $filename = self::getPIDFile();
      if ($filename) {
        $data = getmypid() . '|' . date('H.i.s.d.m.Y');
        if (false !== file_put_contents($filename, $data)) {
          $result = true;
        }
      }
    }
    return $result;
  }

}
