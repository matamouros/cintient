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
 * Because of the way we use __call and $this->_changed, this class shouldn't
 * have null values for class variables populated from the database.
 *
 * One special case with this class is that all data persistence handling is
 * done automatically, i.e., there's no need to call save() from an outside
 * scope. Usage is simple: new objects created from scratch, don't forget to
 * call init(); objects created from the database, no need to do anything.
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
class SystemSettings implements ArrayAccess
{
  private $_settings;
  private $_signature; // Internal flag to control whether a save to database is required

  const ALLOW_USER_REGISTRATION = 'allowUserRegistration'; // bool
  const INTERNAL_BUILDER_ACTIVE = 'internalBuilderActive'; // bool

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
      if ($this->$var !== null) {
        $this->_changed = true;
      }
      $this->$var = $args[0];
      return true;
    }
    return false;
  }

  public function __construct()
  {
    $this->_signature = null;
    //
    // Make sure that bool valued settings are cast to ints.
    //
    $this->_settings = array(
      self::ALLOW_USER_REGISTRATION => 1,
      self::INTERNAL_BUILDER_ACTIVE => CINTIENT_INTERNAL_BUILDER_ACTIVE,
    );
  }

  public function __destruct()
  {
    $this->_save();
  }

  public function offsetSet($offset, $value)
  {
    if (is_null($offset)) {
      $this->_settings[] = $value;
    } else {
      $this->_settings[$offset] = $value;
    }
  }

  public function offsetExists($offset)
  {
    return isset($this->_settings[$offset]);
  }

  public function offsetUnset($offset)
  {
    unset($this->_settings[$offset]);
  }

  public function offsetGet($offset)
  {
    return isset($this->_settings[$offset]) ? $this->_settings[$offset] : null;
  }

  private function _save($force = false)
  {
    if ($this->_getCurrentSignature() == $this->_signature && !$force) {
      SystemEvent::raise(SystemEvent::DEBUG, "Save called, but no saving is required.", __METHOD__);
      return false;
    }

    if (!$stmt = Database::stmtPrepare("REPLACE INTO systemsettings (key, value) VALUES (?,?)")) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems trying to save system settings.", __METHOD__);
      return false;
    }

    foreach ($this->_settings as $key => $value) {
      Database::stmtBind($stmt, array($key, $value));
      if (!Database::stmtExecute($stmt)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving system settings.", __METHOD__);
        return false;
      }
    }

    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved system settings.", __METHOD__);
    #endif
    $this->updateSignature();
    return true;
  }

  static public function get()
  {
    $ret = false;
    $sql = 'SELECT * FROM systemsettings';
    if ($rs = Database::query($sql)) {
      $ret = self::_getObject($rs);
    }
    return $ret;
  }

  /**
   *
   * @param Resultset $rs
   */
  static private function _getObject(Resultset $rs)
  {
    $ret = new self();
    while ($rs->NextRow()) {
      $ret->setSetting($rs->getKey(),$rs->getValue());
    }
    $ret->updateSignature();
    return $ret;
  }

  static public function install()
  {
    $sql = <<<EOT
CREATE TABLE IF NOT EXISTS systemsettings(
  key VARCHAR(255) PRIMARY KEY,
  value TEXT NOT NULL DEFAULT ''
);
EOT;
    if (!Database::execute($sql)) {
      SystemEvent::raise(SystemEvent::INFO, "Could not create SytemSettings related tables.", __METHOD__);
      return false;
    } else {
      $self = new SystemSettings();
      $self->_save(true); // This allows us to save the default system settings values at install time.
      SystemEvent::raise(SystemEvent::INFO, "Created SytemSettings related tables.", __METHOD__);
      return true;
    }
  }

  public function setSetting($key, $value)
  {
    $this->_settings[$key] = $value;
  }

  public function updateSignature()
  {
    $this->setSignature($this->_getCurrentSignature());
  }

  private function _getCurrentSignature()
  {
    $arr = get_object_vars($this);
    $arr['_signature'] = null;
    unset($arr['_signature']);
    return md5(serialize($arr));
  }
}