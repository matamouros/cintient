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
 * A utility class for determining a bunch of information for a system,
 * from a given uname() signature. Originally taken from PEAR, now with
 * a few modifications.
 *
 * For a quick use you are probably looking for Framework_HostOs.
 *
 * @package     Framework
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @author      Stig Bakken <ssb@php.net>
 * @author      Gregory Beaver <cellog@php.net>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     (from PEAR) $Id: Guess.php 299813 2010-05-26 19:50:00Z dufuz $
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Framework_Os
{
  private $_sysname;
  private $_nodename;
  private $_cpu;
  private $_release;

  public function __construct($uname = null)
  {
    list($this->_sysname,
      $this->_release,
      $this->_cpu,
      $this->_nodename) = $this->parseSignature($uname);
  }

  public function parseSignature($uname = null)
  {
    static $sysmap = array(
      'HP-UX' => 'hpux',
      'IRIX64' => 'irix',
    );
    static $cpumap = array(
      'i586' => 'i386',
      'i686' => 'i386',
      'ppc' => 'powerpc',
    );
    if ($uname === null) {
      $uname = php_uname();
    }
    $parts = preg_split('/\s+/', trim($uname));
    $n = count($parts);

    $release  = $machine = $cpu = '';
    $sysname  = $parts[0];
    $nodename = $parts[1];
    $cpu    = $parts[$n-1];
    if ($cpu == 'unknown') {
      $cpu = $parts[$n - 2];
    }

    switch ($sysname) {
      case 'AIX' :
        $release = "$parts[3].$parts[2]";
        break;
      case 'Windows' :
        switch ($parts[1]) {
          case '95/98':
            $release = '9x';
            break;
          default:
            $release = $parts[1];
            break;
        }
        $cpu = 'i386';
        break;
      case 'Linux' :
        // use only the first two digits from the kernel version
        $release = preg_replace('/^([0-9]+\.[0-9]+).*/', '\1', $parts[2]);
        break;
      case 'Mac' :
        $sysname = 'darwin';
        $nodename = $parts[2];
        $release = $parts[3];
        if ($cpu == 'Macintosh') {
          if ($parts[$n - 2] == 'Power') {
            $cpu = 'powerpc';
          }
        }
        break;
      case 'Darwin' :
        if ($cpu == 'Macintosh') {
          if ($parts[$n - 2] == 'Power') {
            $cpu = 'powerpc';
          }
        }
        $release = preg_replace('/^([0-9]+\.[0-9]+).*/', '\1', $parts[2]);
        break;
      default:
        $release = preg_replace('/-.*/', '', $parts[2]);
        break;
    }

    if (isset($sysmap[$sysname])) {
      $sysname = $sysmap[$sysname];
    } else {
      $sysname = strtolower($sysname);
    }
    if (isset($cpumap[$cpu])) {
      $cpu = $cpumap[$cpu];
    }
    return array($sysname, $release, $cpu, $nodename);
  }

  public function getSignature()
  {
    return "{$this->_sysname}-{$this->_release}-{$this->_cpu}";
  }

  public function getSysname()
  {
    return $this->_sysname;
  }

  public function getNodename()
  {
    return $this->_nodename;
  }

  public function getCpu()
  {
    return $this->_cpu;
  }

  public function getRelease()
  {
    return $this->_release;
  }

  public function matchSignature($match)
  {
    $fragments = is_array($match) ? $match : explode('-', $match);
    $n = count($fragments);
    $matches = 0;
    if ($n > 0) {
      $matches += $this->_matchFragment($fragments[0], $this->_sysname);
    }
    if ($n > 1) {
      $matches += $this->_matchFragment($fragments[1], $this->_release);
    }
    if ($n > 2) {
      $matches += $this->_matchFragment($fragments[2], $this->_cpu);
    }
    return ($matches == $n);
  }

  private function _matchFragment($fragment, $value)
  {
    if (strcspn($fragment, '*?') < strlen($fragment)) {
      $reg = '/^' . str_replace(array('*', '?', '/'), array('.*', '.', '\\/'), $fragment) . '\\z/';
      return preg_match($reg, $value);
    }
    return ($fragment == '*' || !strcasecmp($fragment, $value));
  }
}