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
class BuilderElement_Project extends BuilderElementAbstract
{
  protected $_baseDir;
  protected $_defaultTarget;
  protected $_name;
  protected $_properties; // An array with references to Property objects
  protected $_targets;    // An array with references to all target objects
  
  public function __construct()
  {
    $this->_baseDir = null;
    $this->_defaultTarget = null;
    $this->_name = null;
    $this->_properties = array();
    $this->_targets = array();
  }
  
  public function addProperty(BuilderElement_Type_Property $o)
  {
    $this->_properties[] = $o;
  }
  
  public function addTarget(BuilderElement_Target $o)
  {
    $this->_targets[] = $o;
  }
  
  public function isEmpty()
  {
    return empty($this->_targets);
  }
}