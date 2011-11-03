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
 * Copies files and/or directories.
 *
 * @package     Build
 * @subpackage  Task
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Task_Filesystem_Copy extends Build_BuilderElement
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

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    $fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setType(Build_BuilderElement_Type_Fileset::BOTH);
    $fileset->setDefaultExcludes(false);
    $o->setFilesets(array($fileset));
    return $o;
  }

	/**
   * Setter. Makes sure <code>$toDir</code> always ends in a valid
   * <code>DIRECTORY_SEPARATOR</code> token.
   *
   * @param string $dir
   */
  public function setToDir($dir)
  {
    if (!empty($dir) && strpos($dir, DIRECTORY_SEPARATOR, (strlen($dir)-1)) === false) {
      $dir .= DIRECTORY_SEPARATOR;
    }
    $this->_toDir = $dir;
  }

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
      'getHtmlFailOnError' => array(),
      'getHtmlInputText' => array('name' => 'file', 'value' => $this->getFile()),
    	'getHtmlInputText' => array('name' => 'toFile', 'label' => 'Destination file', 'value' => $this->getToFile()),
    	'getHtmlInputText' => array('name' => 'toDir', 'label' => 'Destination dir', 'value' => $this->getToDir()),
    	'getFilesets' => array(),
    );
    parent::toHtml(array('title' => 'Copy'), $callbacks);
  }

  public function toPhing()
  {
    return $this->toAnt();
  }

  public function toPhp(Array &$context = array())
  {
    $php = '';
    if (!$this->getFile() && !$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No source files set for task copy.', __METHOD__);
      return false;
    }

    if (!$this->getToFile() && !$this->getToDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No destination set for task copy.', __METHOD__);
      return false;
    }

    $php .= "
\$GLOBALS['result']['task'] = 'copy';
\$baseToFilename = '';
";

    if ($this->getToFile()) {
      $php .= "
\$path = pathinfo(expandStr('{$this->getToFile()}'));
\$baseToDir = \$path['dirname'];
\$baseToFilename = '/' . \$path['basename']; // pathinfo's dirname *always* returns the dirname without the trailing slash.
";
    } elseif ($this->getToDir()) {
      $php .= "
\$baseToDir = expandStr('{$this->getToDir()}');
";
    }

    //
    // TODO: Potential bug here. If the following generated mkdir does
    // indeed fail and failOnError == true, the execution will continue
    // because we are not returning true... A return true here would
    // halt the generated script execution (that's what return false does
    // in case of error...)
    //
    // Wrapping this whole element into a generated auto-executing closure
    // a la Javascript would be awesome, because that way we could just
    // force a return and not risk shutting down the whole builder script
    //
    $php .= "
if (!file_exists(\$baseToDir) && !@mkdir(\$baseToDir, 0755, true)) {
  output(\"Failed creating dir \$baseToDir.\");
	if ({$this->getFailOnError()}) {
    \$GLOBALS['result']['ok'] = false;
    return false;
  } else {
	  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
  }
}";

    //
    // Internally treat $this->getFile() as a fileset.
    //
    $filesets = array();
    if ($this->getFile()) {
      $getFile = self::_expandStr($this->getFile(), $context);
      $pathFrom = pathinfo($getFile);
      $fileset = new Build_BuilderElement_Type_Fileset();
      if (!file_exists($getFile)) {
        $php .= "
output(\"No such file or directory {$getFile}.\");
if ({$this->getFailOnError()}) { // failonerror
  \$GLOBALS['result']['ok'] = false;
  return false;
} else {
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
}
";
      } elseif (is_file($getFile)) {
        $fileset->addInclude($pathFrom['basename']);
        $fileset->setDir($pathFrom['dirname']);
        $fileset->setType(Build_BuilderElement_Type_Fileset::FILE);
        $php .= "
\$baseFromDir = '{$pathFrom['dirname']}';
";
      } else { // It's a directory
        $fileset->addInclude('**/*');
        $fileset->setDir($getFile);
        $fileset->setType(Build_BuilderElement_Type_Fileset::BOTH); // Very important default!!!
$php .= "
\$baseFromDir = '{$getFile}';
";
      }
      $filesets[] = $fileset;
    } elseif ($this->getFilesets()) { // If file exists, it takes precedence over filesets
      $realFilesets = $this->getFilesets(); // Not to be overwritten
      if (!$realFilesets[0]->getDir() || !$realFilesets[0]->getInclude()) {
        SystemEvent::raise(SystemEvent::ERROR, 'No source files set for task copy.', __METHOD__);
        return false;
      }
      // Iterator mode for copy() must enforce parent dirs before their children,
      // so that we can mkdir the parent without first trying to copy in the children
      // on a non-existing dir.
      $fileset = new Build_BuilderElement_Type_Fileset();
      $fileset->setDir(self::_expandStr($realFilesets[0]->getDir(), $context));
      $fileset->setInclude(explode(' ', self::_expandStr(implode(' ', $realFilesets[0]->getInclude()), $context)));
      $fileset->setExclude(explode(' ', self::_expandStr(implode(' ', $realFilesets[0]->getExclude()), $context)));
      $filesets[] = $fileset;
      $php .= "
\$baseFromDir = '{$fileset->getDir()}';
";
    }

    $php .= "
\$callback = function (\$entry) use (\$baseToDir, \$baseFromDir, \$baseToFilename) {
  \$dest = \$baseToDir . (!empty(\$baseToFilename)?\$baseToFilename:substr(\$entry, strlen(\$baseFromDir)));
  if (is_file(\$entry)) {
    \$ret = @copy(\$entry, \$dest);
  } elseif (is_dir(\$entry)) {
  	if (!file_exists(\$dest) && !@mkdir(\$dest, 0755, true)) {
  	  \$ret = false;
  	} else {
  	  \$ret = true;
    }
  } else {
    \$ret = false;
  }
  if (!\$ret) {
    output(\"Failed copy of \$entry to \$dest.\");
  } else {
    output(\"Copied \$entry to \$dest.\");
  }
  return \$ret;
};
";

    $context['iteratorMode'] = RecursiveIteratorIterator::SELF_FIRST; // Make sure dirs come before their children, in order to be created first
    foreach ($filesets as $fileset) {
      $php .= "
" . $fileset->toPhp($context) . "
if (!fileset{$fileset->getId()}_{$context['id']}(\$callback) && {$this->getFailOnError()}) {
  \$GLOBALS['result']['ok'] = false;
  return false;
} else {
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
}
";
    }
    return $php;
  }
}