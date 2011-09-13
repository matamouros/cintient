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
 *
 * @package     Build
 * @subpackage  Type
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Type_Fileset extends Build_BuilderElement
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

	/**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    return new self();
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

  public function toAnt()
  {
    $xml = new Build_XmlBuilderElement();

    $xml->startElement('fileset');
    if (!$this->getDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Root dir not set for type fileset.', __METHOD__);
      return false;
    }
    if ($this->getDir()) {
      $xml->writeAttribute('dir', $this->getDir());
    }
    if ($this->getDefaultExcludes() !== null) {
      $xml->writeAttribute('defaultexcludes', ($this->getDefaultExcludes()?'yes':'no'));
    }
    if ($this->getId()) {
      $xml->writeAttribute('id', $this->getId());
    }
    if ($this->getInclude()) {
      $includes = $this->getInclude();
      foreach ($includes as $include) {
        $xml->startElement('include');
        $xml->writeAttribute('name', $include);
        $xml->endElement();
      }
    }
    if ($this->getExclude()) {
      $excludes = $this->getExclude();
      foreach ($excludes as $exclude) {
        $xml->startElement('exclude');
        $xml->writeAttribute('name', $exclude);
        $xml->endElement();
      }
    }
    $xml->endElement();
    $ret = $xml->flush();
    $xml = null;
    unset($xml);
    return $ret;
  }

  public function toHtml()
  {
    parent::toHtml();
    if (!$this->isVisible()) {
      return true;
    }
    h::hr();
    $o = $this;
    // Type, radio button
    h::ul(array('class' => 'radioContainer'), function() use ($o) {
      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Files only');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => Build_BuilderElement_Type_Fileset::FILE);
        if ($o->getType() == Build_BuilderElement_Type_Fileset::FILE) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });
      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Dirs only');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => Build_BuilderElement_Type_Fileset::DIR);
        if ($o->getType() == Build_BuilderElement_Type_Fileset::DIR) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });

      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Both');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => Build_BuilderElement_Type_Fileset::BOTH);
        if ($o->getType() == Build_BuilderElement_Type_Fileset::BOTH) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });
    });

    // Default excludes, checkbox
    h::div(array('class' => 'label'), 'Default excludes?');
    h::div(array('class' => 'checkboxContainer'), function() use ($o) {
      $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'defaultExcludes',);
      if ($o->getDefaultExcludes()) {
        $params['checked'] = 'checked';
      }
      h::input($params);
    });
    // Dir, textfield
    h::div(array('class' => 'label'), 'Base dir');
    h::div(array('class' => 'textfieldContainer'), function() use ($o) {
      //h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => (substr($o->getDir(), strlen($GLOBALS['project']->getScmLocalWorkingCopy())))));
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => $o->getDir()));
    });
    $includesLine = '';
    if ($o->getInclude()) {
      $includes = $o->getInclude();
      foreach ($includes as $include) {
        $includesLine .= $include . ', ';
      }
      // TODO: Oh god... Seriously do this better:
      if (!empty($includesLine)) {
        $includesLine = substr($includesLine, 0, strlen($includesLine)-2); // Oh god 2x...
      }
    }
    // Includes, textfield
    h::div(array('class' => 'label'), 'Files/dirs to include');
    h::div(array('class' => 'textfieldContainer'), function() use ($o, $includesLine) {
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'include', 'value' => $includesLine));
    });
    $excludesLine = '';
    if ($o->getExclude()) {
      $excludes = $o->getExclude();
      foreach ($excludes as $exclude) {
        $excludesLine .= $exclude . ', ';
      }
      // TODO: Oh god... Seriously do this better:
      if (!empty($excludesLine)) {
        $excludesLine = substr($excludesLine, 0, strlen($excludesLine)-2); // Oh god...
      }
    }
    // Excludes, textfield
    h::div(array('class' => 'label'), 'Files/dirs to exclude');
    h::div(array('class' => 'textfieldContainer'), function() use ($o, $excludesLine) {
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'exclude', 'value' => $excludesLine));
    });
  }

  public function toPhing()
  {
    return $this->toAnt();
  }

  public function toPhp(Array &$context = array())
  {
    $php = '';
    //
    // Generic class to process the includes/excludes filters
    //
    //TODO: Implement $isCaseSensitive!!!!
    //TODO: Implement only a single top level class for this

    $php = "
if (!class_exists('FilesetFilterIterator', false)) {
  class FilesetFilterIterator extends FilterIterator
  {
    private \$_filesetId;
    private \$_type;

    public function __construct(\$o, \$filesetId, \$type = " . Build_BuilderElement_Type_Fileset::FILE . ")
    {
      \$this->_filesetId = \$filesetId;
      \$this->_type = \$type;
      parent::__construct(\$o);
    }

    public function accept()
    {
      // Check for type, first of all
      if (\$this->_type == " . Build_BuilderElement_Type_Fileset::FILE . " && !is_file(\$this->current()) ||
      		\$this->_type == " . Build_BuilderElement_Type_Fileset::DIR . " && !is_dir(\$this->current()))
      {
        return false;
      }

      // if it is default excluded promptly return false
      foreach (\$GLOBALS['filesets'][\$this->_filesetId]['defaultExcludes'] as \$exclude) {
        if (\$this->_isMatch(\$exclude)) {
          return false;
        }
      }
      // if it is excluded promptly return false
      foreach (\$GLOBALS['filesets'][\$this->_filesetId]['exclude'] as \$exclude) {
        if (\$this->_isMatch(\$exclude)) {
          return false;
        }
      }
      // if it is included promptly return true
      foreach (\$GLOBALS['filesets'][\$this->_filesetId]['include'] as \$include) {
        if (\$this->_isMatch(\$include)) {
          return true;
        }
      }
    }

    private function _isMatch(\$pattern)
    {
      \$current = \$this->current();
      \$dir = \$GLOBALS['filesets'][\$this->_filesetId]['dir'];
      /*if (substr(\$dir, -1) != DIRECTORY_SEPARATOR) {
        \$dir .= DIRECTORY_SEPARATOR;
      }
      \$current = \$dir . \$current;*/
      \$isCaseSensitive = true;
      \$rePattern = preg_quote(\$GLOBALS['filesets'][\$this->_filesetId]['dir'] . \$pattern, '/');
      \$dirSep = preg_quote(DIRECTORY_SEPARATOR, '/');
      \$patternReplacements = array(
        \$dirSep.'\*\*' => '\/?.*',
        '\*\*'.\$dirSep => '.*',
        '\*\*' => '.*',
        '\*' => '[^'.\$dirSep.']*',
        '\?' => '[^'.\$dirSep.']'
      );
      \$rePattern = str_replace(array_keys(\$patternReplacements), array_values(\$patternReplacements), \$rePattern);
      \$rePattern = '/^'.\$rePattern.'$/'.(\$isCaseSensitive ? '' : 'i');
      return (bool) preg_match(\$rePattern, \$current);
    }
  }
}
";
    if (!$this->getDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Root dir not set for type fileset.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}'] = array();
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['dir'] = '';
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['defaultExcludes'] = array(
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
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['exclude'] = array();
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['include'] = array();
";
    if ($this->getDir()) {
      $php .= "
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['dir'] = expandStr('{$this->getDir()}');
";
    }
    if ($this->getDefaultExcludes() === false) {
      $php .= "
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['defaultExcludes'] = array();
";
    }
    if ($this->getInclude()) {
      $includes = $this->getInclude();
      foreach ($includes as $include) {
        $php .= "
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['include'][] = expandStr('{$include}');
";
      }
    }
    if ($this->getExclude()) {
      $excludes = $this->getExclude();
      foreach ($excludes as $exclude) {
        $php .= "
\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['exclude'][] = expandStr('{$exclude}');
";
      }
    }

    $php .= "
if (!function_exists('fileset{$this->getId()}_{$context['id']}')) {
  function fileset{$this->getId()}_{$context['id']}(\$callback)
  {
    \$recursiveIt = false;
    \$dirIt = 'DirectoryIterator';
    \$itIt = 'IteratorIterator';
    foreach (\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['include'] as \$include) {
      /*if (strpos(\$include, '**') !== false ||
         (substr_count(\$include, '/') > 1 && substr_count(\$include, '//') === 0) ||
          substr_count(\$include, '/') == 1 && strpos(\$include, '/') !== 0)
      {*/
        \$recursiveIt = true;
        \$dirIt = 'Recursive' . \$dirIt;
        \$itIt = 'Recursive' . \$itIt;
        break;
      /*}*/
    }
    try {
      foreach (new FilesetFilterIterator(new \$itIt(new \$dirIt(\$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['dir']), (!\$recursiveIt?:" . (!empty($context['iteratorMode'])?:"\$itIt::CHILD_FIRST") . "), (!\$recursiveIt?:\$itIt::CATCH_GET_CHILD)), '{$this->getId()}_{$context['id']}', {$this->getType()}) as \$entry) {
        if (!\$callback(\$entry, \$GLOBALS['filesets']['{$this->getId()}_{$context['id']}']['dir'])) {
          //\$GLOBALS['result']['ok'] = false; // This should be relegated to the caller task
          \$msg = 'Callback applied to fileset returned false [CALLBACK=\$callback] [FILESET={$this->getId()}_{$context['id']}]';
          \$GLOBALS['result']['output'] = \$msg;
          //output(\$msg);
          return false;
        }
      }
    } catch (UnexpectedValueException \$e) { // Typical permission denied
      //\$GLOBALS['result']['ok'] = false; // This should be relegated to the caller task
      \$GLOBALS['result']['output'] = \$e->getMessage();
      output(\$e->getMessage());
      return false;
    }
    return true;
  }
}
";
    return $php;
  }
}