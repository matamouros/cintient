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
 * Changes the permissions on a specific set of files/directories.
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
class Build_BuilderElement_Task_Filesystem_Chmod extends Build_BuilderElement
{
  protected $_file;            // A string containing a single file or dir to act upon
  protected $_mode;            // A string representation of the new permissions (octal, i.e. with a leading 0)
  protected $_filesets;        // An array of fileset types

  public function __construct()
  {
    parent::__construct();
    $this->_file = null;
    $this->_mode = null;
    $this->_filesets = null;
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    $fileset = new Build_BuilderElement_Type_Fileset();
    $o->setFilesets(array($fileset));
    return $o;
  }

  public function toAnt()
  {
    $xml = new XmlDoc();
    $xml->startElement('chmod');
    if (!$this->getFile() && !$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task chmod.', __METHOD__);
      return false;
    }
    $mode = $this->getMode();
    if (empty($mode) || !preg_match('/^\d{3}$/', $mode)) {
      SystemEvent::raise(SystemEvent::ERROR, 'No mode set for task chmod.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('perm', $mode);
    if ($this->getFile()) {
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

  public function toHtml()
  {
    parent::toHtml();
    if (!$this->isVisible()) {
      return true;
    }
    $o = $this;
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      $o->getHtmlTitle(array('title' => 'Chmod'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        // Fail on error, checkbox
        h::div(array('class' => 'label'), 'Fail on error?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnError',);
          if ($o->getFailOnError()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        /*
        // File, textfield
        h::div(array('class' => 'label'), 'File');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'file', 'value' => $o->getFile()));
        });*/
        // Mode, textfield
        h::div(array('class' => 'label'), 'Mode <span class="fineprintLabel">(e.g., 755, 644, 640, etc)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'mode', 'value' => $o->getMode()));
        });
        // Filesets
        if ($o->getFilesets()) {
          $filesets = $o->getFilesets();
          foreach ($filesets as $fileset) {
            $fileset->toHtml();
          }
        }
      });
    });
  }

  public function toPhing()
  {
    $xml = new XmlDoc();
    $xml->startElement('chmod');
    if (!$this->getFile() && !$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task chmod.', __METHOD__);
      return false;
    }
    $mode = $this->getMode();
    if (empty($mode) || !preg_match('/^\d{3}$/', $mode)) {
      SystemEvent::raise(SystemEvent::ERROR, 'No mode set for task chmod.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('mode', $mode);
    if ($this->getFile()) {
      $xml->writeAttribute('file', $this->getFile());
    } elseif ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw($fileset->toPhing());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  public function toPhp(Array &$context = array())
  {
    $php = '';
    if (!$this->getFile() && !$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files not set for task chmod.', __METHOD__);
      return false;
    }
    $mode = $this->getMode();
    if (empty($mode) || !preg_match('/^(?:\d{3}|\$\{\w*\})$/', $mode)) { // It must be a 3 digit decimal or a property
      SystemEvent::raise(SystemEvent::ERROR, 'No mode set for chmod.', __METHOD__);
      return false;
    }

    $php .= "
\$GLOBALS['result']['task'] = 'chmod';
\$callback = function (\$entry) {
  \$getModeInt = expandStr('{$this->getMode()}');
  \$getModeOctal = intval(\$getModeInt, 8); // Casts the decimal string representation into an octal (8 is for base 8 conversion)
  \$ret = @chmod(\$entry, \$getModeOctal);
  if (!\$ret) {
    output(\"Failed setting \$getModeInt on \$entry.\");
  } else {
    output(\"Ok setting \$getModeInt on \$entry.\");
  }
  return \$ret;
};";
    if ($this->getFile()) {
      $php .= "
\$getFile = expandStr('{$this->getFile()}');
if (!\$callback(\$getFile) && {$this->getFailOnError()}) { // failonerror
  \$GLOBALS['result']['ok'] = false;
  return false;
} else {
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
}
";
    } elseif ($this->getFilesets()) { // If file exists, it takes precedence over filesets
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
    }
    return $php;
  }
}