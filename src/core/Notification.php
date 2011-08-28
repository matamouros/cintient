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
 * Commodity class for performing specific system notifications related
 * tasks.
 *
 * @package     Notification
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Notification
{
  /**
   * Returns an array with all the currently available notification
   * methods. It basically iterates the Notification class dir and
   * picks up any available classes there.
   */
  static public function &getMethods()
  {
    $methods = array();
    $dir = CINTIENT_INSTALL_DIR . 'src/core/Notification/';
    foreach (new FilesystemIterator($dir) as $entry) {
      $basename = basename($entry);
      if (strrpos($basename, '.php') !== false) {
        $methods[] = substr($basename, 0, strlen($basename)-4);
      }
    }
    sort($methods);
    return $methods;
  }
}