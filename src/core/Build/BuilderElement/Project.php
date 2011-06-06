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
 * Project builder element. This is the top level builder element, under
 * which all others reside. The project element will always be responsible
 * for calling it's children's toString() methods.
 *
 * @package     Build
 * @subpackage  BuilderElement
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Project extends Build_BuilderElement
{
  protected $_baseDir;
  protected $_defaultTarget;
  protected $_name;
  protected $_properties; // An array with references to Property objects
  protected $_targets;    // An array with references to all target objects

  public function __construct()
  {
    parent::__construct();
    $this->_baseDir = null;
    $this->_defaultTarget = null;
    $this->_name = null;
    $this->_properties = array();
    $this->_targets = array();
  }

  public function addProperty(Build_BuilderElement_Type_Property $o)
  {
    $this->_properties[] = $o;
  }

  public function addTarget(Build_BuilderElement_Target $o)
  {
    $this->_targets[] = $o;
  }

  public function isEmpty()
  {
    return empty($this->_targets);
  }
}