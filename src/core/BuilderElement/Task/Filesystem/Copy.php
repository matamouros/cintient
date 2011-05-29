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
class BuilderElement_Task_Filesystem_Copy extends BuilderElement
{
  protected $_file;       // A *file* to copy. If more complexity is required, use the fileset
  protected $_overwrite;  // Overwrite destination, even if it is newer
  protected $_toDir;
  protected $_toFile;
  protected $_filesets;
  
  public function __construct()
  {
    parent::__construct();
    $this->_file = null;
    $this->_overwrite = false;
    $this->_toFile = null;
    $this->_toDir = null;
    $this->_filesets = array();
  }
}