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
 * Searches for a particular regular expression inside a single or group
 * of files and replaces it.
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
class Build_BuilderElement_Task_ReplaceRegexp extends Build_BuilderElement
{
  protected $_file;       // A single file to search in
  protected $_filesets;   // A set of files to search in
  protected $_match;      // The regular expression pattern to match in the file(s)
  protected $_replace;    // The substitution pattern to place in the file(s)
  protected $_flags;      // PCRE flags:
                          //    g (global), replaces all occurrences
                          //    i (case insensitive)
                          //    m (multiline), '^' and '$' mark start and end of *each line* in string
                          //    s (singleline), '^' and '$' mark start/end of string and '.' matches newlines

  public function __construct()
  {
    parent::__construct();
    $this->_file = null;
    $this->_filesets = array();
    $this->_match = null;
    $this->_replace = null;
    $this->_flags = null;
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    $fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setType(Build_BuilderElement_Type_Fileset::FILE);
    $fileset->setDefaultExcludes(false);
    $o->setFilesets(array($fileset));
    return $o;
  }

  public function toAnt()
  {
    $xml = new XmlDoc();
    $xml->startElement('replaceregexp');
    if (empty($this->_filesets) && empty($this->_file)) {
      SystemEvent::raise(SystemEvent::INFO, 'No files set for task replaceregexp.', __METHOD__);
      return false;
    }
    if (empty($this->_match)) {
      SystemEvent::raise(SystemEvent::INFO, 'Match attribute is mandatory for task replaceregexp.', __METHOD__);
      return false;
    }
    if (empty($this->_replace)) {
      SystemEvent::raise(SystemEvent::INFO, 'Replace attribute is mandatory for task replaceregexp.', __METHOD__);
      return false;
    }
    /*
    // TODO: Check Ant for existence of failonerror attribute
    if ($this->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($this->getFailOnError()?'true':'false'));
    }*/
    $xml->writeAttribute('match', $this->getMatch());
    $xml->writeAttribute('replace', $this->getReplace());
    if (!empty($this->_flags)) {
      $xml->writeAttribute('flags', $this->getFlags());
    }
    if (!empty($this->_file)) {
      $xml->writeAttribute('file', $this->getFile());
    } elseif ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw($fileset->toAnt());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  public function toHtml(Array $_ = array(), Array $__ = array())
  {
    if (!$this->isVisible()) {
      return true;
    }
    $callbacks = array(
      array('cb' => 'getHtmlFailOnError'),
      // Match, textfield
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'match',
    		'value' => html_entity_decode($this->getMatch(), ENT_QUOTES),
    		'help' => 'The regular expression pattern to match in the file(s), PCRE compatible. Regexes should be PHP PCRE for the integration builder or any other for the deployment builder.',
      ),
      // Replace, textfield
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'replace',
    		'value' => html_entity_decode($this->getReplace(), ENT_QUOTES),
    		'help' => 'The substitution pattern to place in the file(s). Regexes should be PHP PCRE for the integration builder or any other for the deployment builder.',
      ),
      // Flags, textfield
    	array(
    	  'cb' => 'getHtmlInputText',
    		'name' => 'flags',
    		'value' => $this->getFlags(),
     		'help' => 'The flags of the regexp engine. For the integration builder use PHP PCRE: g (global), i (case insensitive), m (multiline) and s (singleline).',
      ),
      // File, textfield
      array(
      	'cb' => 'getHtmlInputText',
      	'name' => 'file',
      	'value' => $this->getFile()
      ),
      // Filesets
    	array('cb' => 'getFilesets'),
    );
    parent::toHtml(array('title' => 'ReplaceRegexp'), $callbacks);
  }

  public function toPhing()
  {
    $str = <<<EOT

<!--
Due to Phing considering ReplaceRegexp a filter (that tipically is not a
task in itself, but rather applied to the context of a task - like for instance
when you are copying files), Cintient currently doesn't support exporting
ReplaceRegexp tasks to Phing.
-->

EOT;
    return $str;
  }

  public function toPhp(Array &$context = array())
  {
    $php = "
\$GLOBALS['result']['task'] = 'replaceregexp';
";
    if (empty($this->_filesets) && empty($this->_file)) {
      $msg = 'No files set for task replaceregexp.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      $php .= "
output('{$msg}');
\$GLOBALS['result']['ok'] = false;
return false;
";
      return $php;
    }
    if (empty($this->_match)) {
      $msg = 'Match attribute is mandatory for task replaceregexp.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      $php .= "
output('{$msg}');
\$GLOBALS['result']['ok'] = false;
return false;
";
      return $php;
    }
    if (empty($this->_replace)) {
      $msg = 'Replace attribute is mandatory for task replaceregexp.';
      SystemEvent::raise(SystemEvent::INFO, $msg, __METHOD__);
      $php .= "
output('{$msg}');
\$GLOBALS['result']['ok'] = false;
return false;
";
      return $php;
    }

    $php .= "
\$callback = function (\$entry) {
  \$ret = true;
  if (is_file(\$entry)) { // ignore anything other than files
    if (\$fileContent = file_get_contents(\$entry)) {
      \$replaces = 0;
    	\$newContent = preg_replace('/".preg_replace("(')", "\'", self::_expandStr(html_entity_decode($this->getMatch(), ENT_QUOTES), $context))."/', '".preg_replace("(')", "\'", self::_expandStr(html_entity_decode($this->getReplace(), ENT_QUOTES), $context))."', \$fileContent, -1, \$replaces); // Let the user know that he must escape singlequotes in the match textfield
    	if (\$replaces > 0) {
    		\$replaces = (\$replaces == 1 ? '' : 'es'); // Ugly code for nice output messages
    		if (!file_put_contents(\$entry, \$newContent)) {
    			output(\"Found replaceable match\$replaces, but couldn't update file \$entry.\");
    			\$ret = false;
    	  } else {
    	    output(\"Replaced match\$replaces in \$entry.\");
  			}
      } else {
      	output(\"No matches in \$entry.\");
      }
    }
  }
  return \$ret;
};
";
    if ($this->getFile()) {
      $getFile = self::_expandStr($this->getFile(), $context);
      $pathFrom = pathinfo($getFile);
      $fileset = new Build_BuilderElement_Type_Fileset();
      $fileset->addInclude($pathFrom['basename']);
      $fileset->setDir($pathFrom['dirname']);
      $fileset->setType(Build_BuilderElement_Type_Fileset::FILE);
      // If File is set, it takes precedence over any set filesets,
      // for simplification purposes
      $this->setFilesets(array($fileset));
    }
    $filesets = $this->getFilesets();
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