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
  protected $_signature;    // Internal flag to control whether a save to database is required

  public function __construct()
  {
    $this->_signature = null;
  }

  public function __destruct()
  {
    $this->_save();
  }

  public function __sleep()
  {
    $toSleep = parent::__sleep();
    // super already gives us the serializable attributes, we just need
    // to remove _signature here.
    if (($pos = array_search('_signature', $toSleep)) !== false) {
      unset($toSleep[$pos]);
    }
    return $toSleep;
  }

  protected function _getCurrentSignature(array $exclusions = array())
  {
    //SystemEvent::raise(SystemEvent::DEBUG, "Called. [OBJ=".get_class($this)."]", __METHOD__);
    $sigVars = array();
    $objVars = get_object_vars($this);
    foreach ($objVars as $key => $objVar) {
      // TODO: Do this properly and, as __sleep(), make super return a
      // list of to-sign attributes (with _ptr* already excluded) and
      // have this just remove _signature. _ptr should be super's
      // jurisdiction. Probably we need to setup a method in super just
      // for returning the list of to-sign attributes, but yet unsigned.
      if ($key != '_signature' && strpos($key, '_ptr') !== 0) {
        $sigVars[$key] = $objVar;
      }
    }
    return md5(serialize($sigVars));
  }

  public function hasChanged()
  {
    if ($this->_getCurrentSignature() == $this->_signature) {
      //SystemEvent::raise(SystemEvent::DEBUG, "Object not changed [OBJ=".get_class($this)."]", __METHOD__);
      return false;
    }
    //SystemEvent::raise(SystemEvent::DEBUG, "Object changed [OBJ=".get_class($this)."]", __METHOD__);
    return true;
  }

  public function resetSignature()
  {
    $this->setSignature($this->_getCurrentSignature());
  }

  abstract public function init();

  abstract protected function _save($force = false);
}
