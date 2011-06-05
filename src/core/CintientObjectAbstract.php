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
 * @package Cintient
 */
abstract class CintientObjectAbstract
{
  private $_signature;    // Internal flag to control whether a save to database is required

  /**
   * Magic method implementation for calling vanilla getters and setters. This
   * is rigged to work only with private/protected non-static class variables
   * whose nomenclature follows the Zend Coding Standard.
   *
   * @param $name
   * @param $args
   */
  public function __call($name, $args)
  {
    if (strpos($name, 'get') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      return $this->$var;
    } elseif (strpos($name, 'set') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      $this->$var = $args[0];
      return true;
    } elseif (strpos($name, 'is') === 0) {
      $var = '_' . lcfirst(substr($name, 2));
      return (bool)$this->$var;
    } elseif (strpos($name, 'getDate') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      if (empty($this->$var)) {
        $this->$vat = date('Y-m-d H:i:s');
      }
      return $this->$var;
    } else {
      trigger_error("No valid method available for calling", E_USER_ERROR);
      exit;
    }
    return false;
  }

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
