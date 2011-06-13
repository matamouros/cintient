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
 * Handy class for quickly determining the host OS where Cintient is
 * running on.
 *
 * @package     Framework
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Framework_HostOs
{
  /**
   * Consts for external easy referencing the most common system names.
   */
  const AIX = 'aix';
  const WINDOWS = 'windows';
  const LINUX = 'linux';
  const MAC = 'darwin';

  private static function _singleton()
  {
    static $instance;
    if (!($instance instanceof Framework_Os)) {
      $instance = new Framework_Os();
    }
    return $instance;
  }

  static public function isWindows()
  {
    $os = self::_singleton();
    return ($os->getSysname() == self::WINDOWS);
  }
}