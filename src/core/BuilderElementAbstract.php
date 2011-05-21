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
  protected $_internalId;
  
  public function __construct()
  {
    $this->_internalId = Utility::generateRandomString() . uniqid();
  }
  
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

  public function getElement($id)
  {
    if ($id == $this->_internalId) {
      return $this;
    }
    $internalId = null;
    $attributes = get_object_vars($this);
    foreach ($attributes as $attribute) {
      if (is_object($attribute)) { // Go to the next nested level
        $internalId = $attribute->getElement($id);
      } elseif (is_array($attribute)) { // Tear down the array
        if (!function_exists('f')) {
          //
          // Tears down an array until an element or the end is found
          //
          function f($array, $id)
          {
            $internalId = null;
            foreach ($array as $attribute) {
              if (is_object($attribute)) {
                $internalId = $attribute->getElement($id); // Go down one level
              } elseif (is_array($attribute)) {
                $internalId = f($attribute, $id); // Tear down the array
              }
              if (!empty($internalId)) {
                break;
              }
            }
            return $internalId;
          }
        }
        $internalId = f($attribute, $id);
      }
      if (!empty($internalId)) {
        break;
      }
    }
    return $internalId;
  }
  
  /**
   * Deletes an element with a given ID from the current builder elements
   * hierarchical tree, wherever it may be. It actually implements a
   * step by step slow copy of the whole tree, leaving out only the
   * element referenced by the given id.
   */
  public function deleteElement($id)
  {
    $class = get_class($this);
    $dest = new $class();
    $attributes = get_object_vars($this);

    //
    // Closure for drilling down on an array
    //
    $f = function ($array, $id)
    {
      $ret = array();
      foreach ($array as $attributeName => $attribute) {
        if (is_object($attribute) && $attribute->getInternalId() == $id) {
          continue;
        } elseif (is_array($attribute)) {
          array_push($ret, call_user_func(__FUNCTION__, $attribute, $id));
        } elseif (is_object($attribute)) {
          array_push($ret, $attribute->deleteElement($id));
        } else {
          array_push($ret, $attribute);
        }
      }
      return $ret;
    };
    
    //
    // This foreach processes this element's attributes and delegates
    // work on whatever is found:
    // . an array attribute is delivered to the above closure
    // . an object is delivered recursively to this method again
    // . a simple type is copied directly
    //
    foreach ($attributes as $attributeName => $attribute) {
      if (is_object($attribute) && $attribute->getInternalId() == $id) {
        continue;
      } elseif (is_array($attribute)) {
        $dest->$attributeName = $f($attribute, $id);
      } elseif (is_object($attribute)) {
        $dest->$attributeName = $attribute->deleteElement($id);
      } else {
        $dest->$attributeName = $attribute;
      }
    }
    return $dest;
  }
}