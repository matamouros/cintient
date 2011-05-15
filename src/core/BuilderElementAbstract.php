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
 *
 */
class BuilderElementAbstract
{
  public function __call($name, $args)
  {
    if (strpos($name, 'get') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      return $this->$var;
    } elseif (strpos($name, 'set') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      $this->$var = $args[0];
      return true;
    }
    trigger_error("No valid method available for calling [METHOD={$name}]", E_USER_ERROR);
    exit;
  }
  
  public function toString($connectorType)
  {
    $method = get_class($this);
    $class = 'BuilderConnector_' . ucfirst($connectorType);
    //TODO: The following check is not working... wtf?!
    /*
    if (!class_exists($class) || method_exists($class, $method)) {
      trigger_error("Wrong connector type passed or wrong element being invoked. [CONNECTOR={$connectorType}] [ELEMENT={$method}]", E_USER_ERROR);
      return false;
    }*/
    return $class::$method($this);
  }
  
  public function toHtml()
  {
    $method = get_class($this);
    $class = 'BuilderConnector_Html';
    return $class::$method($this);
  }
}