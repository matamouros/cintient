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
 * Helper class for handling filesystem operations.
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
class Framework_Filesystem
{
  /**
   *
   * Enter description here ...
   * @param unknown_type $dir
   */
  static public function emptyDir($dir) {
    $ret = true;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $path) {
      if ($path->isDir()) {
        if ($res = @rmdir($path->__toString())) {
          SystemEvent::raise(SystemEvent::DEBUG, "Removed dir {$path->__toString()}", __METHOD__);
        } else {
          SystemEvent::raise(SystemEvent::ERROR, "Couldn't remove dir {$path->__toString()}", __METHOD__);
        }
        $ret = $ret & $res;
      } else {
        if ($res = @unlink($path->__toString())) {
          SystemEvent::raise(SystemEvent::DEBUG, "Removed file {$path->__toString()}", __METHOD__);
        } else {
          SystemEvent::raise(SystemEvent::ERROR, "Couldn't remove file {$path->__toString()}", __METHOD__);
        }
        $ret = $ret & $res;
      }
    }
    return $ret;
  }

  /**
   *
   * Enter description here ...
   * @param unknown_type $dir
   * @return boolean
   */
  static public function removeDir($dir)
  {
    return (self::emptyDir($dir) && @rmdir($dir));
  }
}