<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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
 * Helper class for handling filesystem iteration with a set of filters.
 *
 * @package     Framework
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Framework_FilesystemFilterIterator extends FilterIterator
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
  private $_type;

  const FILE = 0;
  const DIR  = 1;
  const BOTH = 2;


	/**
	 * Due to the single inheritance model of PHP and traits being
	 * available only on 5.4 and up, we can't extend Framework_BaseObject.
	 *
   * @see Framework_BaseObject
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
    } elseif (strpos($name, 'is') === 0) {
      $var = '_' . lcfirst(substr($name, 2));
      return (bool)$this->$var;
    } elseif (strpos($name, 'getDate') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      if (empty($this->$var)) {
        $this->$var = date('Y-m-d H:i:s');
      }
      return $this->$var;
    } elseif (strpos($name, 'empty') === 0) {
      $var = '_' . lcfirst(substr($name, 5));
      return empty($this->$var);
    } else {
      trigger_error("No valid method available for calling", E_USER_ERROR);
      exit;
    }
  }

  public function __construct(Iterator $it, $dir, Array $include = array(), Array $exclude = array(), $type = self::BOTH, $defaultExcludes = true)
  {
    parent::__construct($it);
    $this->_dir = $dir;
    $this->_dir = str_replace(array('\\','//'), '/',$this->_dir);
    $this->_include = $include;
    $this->_exclude = $exclude;
    $this->_type = $type;
    if (!$defaultExcludes) {
      $this->_defaultExcludes = array();
    }
  }

  public function accept()
  {
    // Check for type, first of all
    if (($this->_type == self::FILE && !is_file($this->current())) ||
        ($this->_type == self::DIR  && !is_dir($this->current())))
    {
      return false;
    }

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
    $current = str_replace(array('\\','//'), '/',$current);
    $dir = $this->_dir;
    if (substr($dir, -1) != '/') {
      $dir .= '/';
    }
    $current = $dir . $current;
    $isCaseSensitive = true;
    $rePattern = preg_quote($this->_dir . $pattern, '/');
    $dirSep = preg_quote('/', '/');
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