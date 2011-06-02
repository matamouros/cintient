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
 * @package System
 */
class Config
{
  //TODO: this should instead be in /etc/
  const CONFIGURATION_FILE = "";// = BASE_DIR . 'etc/cintient.ini';

  /**
   *
   */
  static public function load()
  {
    //TODO: what happens when the file doesn't exist?
    $config = parse_ini_file(self::CONFIGURATION_FILE);
    if (!self::_validate($config)) {
      //TODO: error log
      return false;
    }
    return $config;
  }

  /**
   *
   * @param array $config
   */
  static private function _validate($config)
  {
    return true;
  }
}