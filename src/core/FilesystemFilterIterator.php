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
 * @author pfonseca
 * @package Utility
 */
class FilesystemFilterIterator extends FilterIterator
{
  /**
   * An array of default exclusions to not consider on accept()
   */
  private $_defaultExcludes = array(
    '**/*~',
    '**/#*#',
    '**/.#*',
    '**/%*%',
    '**/._*',
    '**/CVS',
    '**/CVS/**',
    '**/.cvsignore',
    '**/SCCS',
    '**/SCCS/**',
    '**/vssver.scc',
    '**/.svn',
    '**/.svn/**',
    '**/.DS_Store',
    '**/.git',
    '**/.git/**',
    '**/.gitattributes',
    '**/.gitignore',
    '**/.gitmodules',
    '**/.hg',
    '**/.hg/**',
    '**/.hgignore',
    '**/.hgsub',
    '**/.hgsubstate',
    '**/.hgtags',
    '**/.bzr',
    '**/.bzr/**',
    '**/.bzrignore',
  );

  private $_dir;

  private $_exclude;

  private $_include;


	/**
   * Magic method implementation for calling vanilla getters and setters. This
   * is rigged to work only with private/protected non-static class variables
   * whose nomenclature follows the Zend Coding Standard.
   *
   * @param $name
   * @param $args
   */
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
    return false;
  }

  public function __construct(Iterator $it, $dir, Array $include = array(), Array $exclude = array())
  {
    parent::__construct($it);
    $this->_dir = $dir;
    $this->_include = $include;
    $this->_exclude = $exclude;
  }

  public function accept()
  {
    // if it is default excluded promptly return false
    foreach ($this->_defaultExcludes as $exclude) {
      if ($this->_isMatch($exclude)) {
        return false;
      }
    }
    // if it is excluded promptly return false
    foreach ($this->_exclude as $exclude) {
      if ($this->_isMatch($exclude)) {
        return false;
      }
    }
    // if it is included promptly return true
    foreach ($this->_include as $include) {
      if ($this->_isMatch($include)) {
        return true;
      }
    }
    return false;
  }

  private function _isMatch($pattern)
  {
    $current = $this->current();
    $dir = $this->_dir;
    if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
      $dir .= DIRECTORY_SEPARATOR;
    }
    $current = $dir . $current;
    $isCaseSensitive = true;
    $rePattern = preg_quote($this->_dir . $pattern, '/');
    $dirSep = preg_quote(DIRECTORY_SEPARATOR, '/');
    $patternReplacements = array(
      $dirSep.'\*\*' => '\/?.*',
      '\*\*'.$dirSep => '.*',
      '\*\*' => '.*',
      '\*' => '[^'.$dirSep.']*',
      '\?' => '[^'.$dirSep.']'
    );
    $rePattern = str_replace(array_keys($patternReplacements), array_values($patternReplacements), $rePattern);
    $rePattern = '/^'.$rePattern.'$/'.($isCaseSensitive ? '' : 'i');
    return (bool) preg_match($rePattern, $current);
  }
}