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
class BuilderElement
{
  protected $_active;         // Indicates if this builder element should be considered executable
  protected $_deletable;      // Special system builder elements might not be deleted by the user. Not user setable.
  protected $_editable;       // Special system builder elements might not be editable by the user. Not user setable.
  protected $_failOnError;
  protected $_internalId;
  protected $_visible;        // Special system builder elements might not be visible by the user. Not user setable.

  public function __construct()
  {
    $this->_active = true;
    $this->_deletable = true;
    $this->_editable = true;
    $this->_failOnError = true;
    $this->_internalId = Utility::generateRandomString() . uniqid();
    $this->_visible = true;
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
    } elseif (strpos($name, 'is') === 0) {
      $var = '_' . lcfirst(substr($name, 2));
      return (bool)$this->$var;
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
   * I'm pretty sure I will one day look at this method and either cry or laugh...
   * Don't publicly call this with the second parameter set. It's for
   * internal purposes only... Oh my...
   *
   */
  public function getParent($id, &$parentElement = null)
  {
    if ($id == $this->_internalId) {
      return $this;
    }
    $attributes = get_object_vars($this);
    $currentElement = $this;
    $oldParent = $parentElement;
    foreach ($attributes as $attribute) {
      if (is_object($attribute)) { // Go to the next nested level
        $parentElement = $currentElement;
        $res = $attribute->getParent($id, $parentElement);
      } elseif (is_array($attribute)) { // Tear down the array
        //
        // Tears down an array until an element or the end is found
        //
        $f = function ($array, $id) use (&$parentElement)
        {
          $res = null;
          foreach ($array as $attribute) {
            if (is_object($attribute)) {
              $res = $attribute->getParent($id, $parentElement); // Go down one level
            } elseif (is_array($attribute)) {
              $res = call_user_func(__FUNCTION__, $attribute, $id); // Tear down the array
            }
            if (!empty($res)) {
              return $res;
            }
          }
          return false;
        };
        $parentElement = $currentElement;
        $res = $f($attribute, $id);
      }
      if (!empty($res)) {
        return $parentElement;
      } else {
        $parentElement = $oldParent;
      }
    }
    return false;
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

  public function sortElements(Array $orderedIds, Array &$elements = array())
  {
    $s = function (Array $unorderedElements, Array $orderedIds, Array &$orderedElements)
    {
      $orderedElements = array();
      foreach ($orderedIds as $id) {
        foreach ($unorderedElements as $element) {
          if ($element->getInternalId() == $id) {
            $orderedElements[] = $element;
            $element = null;
            unset($element);
            break;
          }
        }
      }
    };

    $id = $orderedIds[0];
    if ($id == $this->_internalId) {
      return $this;
    }
    $internalId = null;
    $attributes = get_object_vars($this);
    foreach ($attributes as $name => $attribute) {
      if (is_object($attribute)) { // Go to the next nested level
        $internalId = $attribute->sortElements($orderedIds, $elements);
        if (!empty($elements)) {
          return $elements;
        } elseif (!empty($internalId)) {
          $s($attributes, $orderedIds, $elements);
          //return $elements;
        }
      } elseif (is_array($attribute)) { // Tear down the array
        //
        // Tears down an array until an element or the end is found
        //
        $f = function ($array, $id, &$elements) use ($orderedIds, $s)
        {
          $internalId = null;
          foreach ($array as $attribute) {
            if (is_object($attribute)) {
              $internalId = $attribute->sortElements($orderedIds, $elements); // Go down one level
              if (!empty($elements)) {
                return $elements;
              } elseif (!empty($internalId)) {
                $s($array, $orderedIds, $elements);
                //return $elements;
              }
            } elseif (is_array($attribute)) {
              $internalId = call_user_func(__FUNCTION__, $attribute, $id, $elements); // Tear down the array
            }
          }
        };
        $internalId = $f($attribute, $id, $elements);
      }
    }
    return $elements;
  }
}