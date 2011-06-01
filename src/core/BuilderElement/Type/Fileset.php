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
 * The Fileset builder element as an extended functionality to deal with
 * files only, dirs only or both. In practice this means that it can
 * alone emulate an Ant Fileset and a Dirset. On the other hand Phing
 * only supports Fileset (although I don't quite know if it embeds Dirset
 * functionality)
 */
class BuilderElement_Type_Fileset extends BuilderElement
{
  protected $_dir;             // The root dir of this fileset
  protected $_defaultExcludes; // Set of definitions that are excluded from all matches (.svn, .CVS, etc)
  protected $_id;
  protected $_include;
  protected $_exclude;
  protected $_type;            // Either FILE, DIR or BOTH (defaults to both)

  const FILE = 0;
  const DIR  = 1;
  const BOTH = 2;

  public function __construct()
  {
    parent::__construct();
    $this->_dir = null;
    $this->_file = null;
    $this->_defaultExcludes = true;
    $this->_id = null;
    $this->_include = array();
    $this->_exclude = array();
    $this->_type = self::getDefaultType();
  }

  static public function getDefaultType()
  {
    return self::FILE;
  }

  public function getId()
  {
    if (empty($this->_id)) {
      $this->setId(uniqid('fs'));  // Make sure a unique ID is always available
    }
    return $this->_id;
  }

  /**
   * All '/' and '\' characters are replaced by DIRECTORY_SEPARATOR, so the
   * separator used need not match DIRECTORY_SEPARATOR. Correctly treats a new
   * include rule. Adds "**" if the include is a dir, so as to process all it's
   * children.
   *
   * Loosely based on phing's DirectoryScanner::setExcludes.
   *
   * @param string $exclude
   */
  public function addExclude($exclude)
  {
    $pattern = null;
    $pattern = str_replace('\\', DIRECTORY_SEPARATOR, $exclude);
    $pattern = str_replace('/', DIRECTORY_SEPARATOR, $pattern);
    if (strpos($pattern, DIRECTORY_SEPARATOR, (strlen($pattern)-1)) !== false) {
      $pattern .= "**";
    }
    $this->_exclude[] = $pattern;
  }

  /**
   * All '/' and '\' characters are replaced by <code>DIRECTORY_SEPARATOR</code>, so the
   * separator used need not match <code>DIRECTORY_SEPARATOR</code>. Correctly treats a new
   * include rule. Adds "**" if the include is a dir, so as to process all it's
   * children.
   *
   * Loosely based on phing's DirectoryScanner::setIncludes.
   *
   * @param string $include
   */
  public function addInclude($include)
  {
    $pattern = null;
    $pattern = str_replace('\\', DIRECTORY_SEPARATOR, $include);
    $pattern = str_replace('/', DIRECTORY_SEPARATOR, $pattern);
    if (strpos($pattern, DIRECTORY_SEPARATOR, (strlen($pattern)-1)) !== false) {
      $pattern .= "**";
    }
    $this->_include[] = $pattern;
  }

  /**
   * Whenever empty, we default to all.
   * TODO: This is probably not the best way to implement it
   */
  public function getInclude()
  {
    if (empty($this->_include)) {
      $this->_include = array('**/*');
    }
    return $this->_include;
  }

  /**
   * Setter. Makes sure <code>$dir</code> always ends in a valid
   * <code>DIRECTORY_SEPARATOR</code> token.
   *
   * @param string $dir
   */
  public function setDir($dir)
  {
    if (!empty($dir) && strpos($dir, DIRECTORY_SEPARATOR, (strlen($dir)-1)) === false) {
      $dir .= DIRECTORY_SEPARATOR;
    }
    $this->_dir = $dir;
  }
}