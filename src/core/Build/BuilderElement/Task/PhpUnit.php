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
 * Usage:
 *
 * @package Builder
 */
class Build_BuilderElement_Task_PhpUnit extends Build_BuilderElement
{
  protected $_codeCoverageXmlFile;
  protected $_codeCoverageHtmlDir;
  protected $_failOnFailure;       // PHPUnit distinguishes failures from errors
  protected $_failOnIncomplete;
  protected $_failOnSkipped;
  protected $_filesets;            // An array of fileset types
  protected $_logJunitXmlFile;

  public function __construct()
  {
    parent::__construct();
    $this->_codeCoverageXmlFile = null;
    $this->_codeCoverageHtmlDir = null;
    $this->_failOnFailure = true;
    $this->_failOnIncomplete = true;
    $this->_failOnSkipped = false;
    $this->_filesets = null;
    $this->_logJunitXmlFile = null;
  }
}