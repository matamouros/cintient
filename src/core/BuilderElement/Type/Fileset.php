<?php
/*
 * Cintient, Continuous Integration made simple.
 * 
 * Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * . Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * . Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 *   
 * . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
 *   the names of its contributors may be used to endorse or promote
 *   products derived from this software without specific prior
 *   written permission.
 *   
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 */

/**
 * 
 */
class BuilderElement_Type_Fileset extends BuilderElementAbstract
{
  protected $_dir;             // The root dir of this fileset
  protected $_defaultExcludes; // Set of definitions that are excluded from all matches (.svn, .CVS, etc)
  protected $_id;
  protected $_include;
  protected $_exclude;
  
  public function __construct()
  {
    $this->_dir = null;
    $this->_file = null;
    $this->_defaultExcludes = null;
    $this->_id = null;
    $this->_include = array();
    $this->_exclude = array();
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
    if (strpos($dir, DIRECTORY_SEPARATOR, (strlen($dir)-1)) === false) {
      $dir .= DIRECTORY_SEPARATOR;
    }
    $this->_dir = $dir;
  }
}