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
 * Base class with database specific functionality, namely auto-save.
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
abstract class Framework_DatabaseObjectAbstract extends Framework_BaseObject
{
  private $_signature;    // Internal flag to control whether a save to database is required

  public function __construct()
  {
    $this->_signature = null;
  }

  public function __destruct()
  {
    $this->_save();
  }

  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    return md5(serialize($arr));
  }

  public function hasChanged()
  {
    if ($this->_getCurrentSignature() == $this->_signature) {
      SystemEvent::raise(SystemEvent::DEBUG, "No changes detected.", __METHOD__);
      return false;
    }
    return true;
  }

  public function resetSignature()
  {
    $this->setSignature($this->_getCurrentSignature());
  }

  abstract public function init();

  abstract protected function _save($force = false);
}
