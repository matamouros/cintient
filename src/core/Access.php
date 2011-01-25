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
 * A class for handling access level.
 */
class Access
{
  const NONE  = 0; // Not even owner has access
  const READ  = 1;
  const BUILD = 2;
  const WRITE = 4;
  const OWNER = 8;
  
  static public function toStr($access)
  {
    $str = '';
    switch ($access) {
      case self::READ:
        $str = 'read';
        break;
      case self::BUILD:
        $str = 'build';
        break;
      case self::WRITE:
        $str = 'write';
        break;
      case self::OWNER:
        $str = 'owner';
        break;
      case self::NONE:
      default:
        $str = 'none';
        break;
    }
    return $str;
  }
  
  static public function getList()
  {
    return array(
      self::NONE => self::toStr(self::NONE),
      self::READ => self::toStr(self::READ),
      self::BUILD => self::toStr(self::BUILD),
      self::WRITE => self::toStr(self::WRITE),
      self::OWNER => self::toStr(self::OWNER),
    ); 
  }
}