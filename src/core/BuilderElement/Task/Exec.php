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
 * $exec = new BuilderElement_Task_Exec();
 * $exec->setExecutable('php');
 * $exec->setArgs(array('runMe.php arg1 arg2'));
 * $exec->setDir('/tmp/');
 * $exec->setOutputProperty('fooBar');
 * echo $exec->toString('ant');
 */
class BuilderElement_Task_Exec extends BuilderElementAbstract
{
  protected $_executable; 
  protected $_args;            // The arguments to the executable command, if any, a space separated string
  protected $_dir;             // The directory in which the command should be executed in
  protected $_outputProperty;  // Log the command's output to the variable with this name
  protected $_failOnError;     // Stop the build if the command fails (Ant only)
  
  public function __construct()
  {
    $this->_executable = null;
    $this->_args = null;
    $this->_dir = null;
    $this->_outputProperty = null;
    $this->_failOnError = null;
  }
}