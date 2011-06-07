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
 * Delete task is responsible for deletion of files and directories from
 * the filesystem
 *
 * @package     Build
 * @subpackage  Filesystem
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Task_Filesystem_Delete extends Build_BuilderElement
{
  protected $_includeEmptyDirs;
  protected $_filesets;          // An array of fileset types

  public function __construct()
  {
    parent::__construct();
    $this->_includeEmptyDirs = false;
    $this->_filesets = null;
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    $o = new self();
    $o->setIncludeEmptyDirs(true);
    $fileset = new Build_BuilderElement_Type_Fileset();
    $fileset->setType(Build_BuilderElement_Type_Fileset::BOTH);
    $fileset->setDefaultExcludes(false);
    $o->setFilesets(array($fileset));
    return $o;
  }

  public function toAnt()
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('delete');
    if (!$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task delete.', __METHOD__);
      return false;
    }
    if ($this->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($this->getFailOnError()?'true':'false'));
    }
    if ($this->getIncludeEmptyDirs()) {
      $xml->writeAttribute('includeemptydirs', ($this->getIncludeEmptyDirs()?'true':'false'));
    }
    if ($this->getFilesets()) {
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
      $o->getHtmlTitle(array('title' => 'Delete'));
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
        // Fail on error, checkbox
        h::div(array('class' => 'label'), 'Include empty dirs?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'includeEmptyDirs',);
          if ($o->getIncludeEmptyDirs()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
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
    return $this->toAnt();
  }

  public function toPhp(Array &$context = array())
  {
    $php = '';
    if (!$this->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files not set for task delete.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['result']['task'] = 'delete';
";
    if ($this->getFilesets()) {
      $filesets = $this->getFilesets();
      foreach ($filesets as $fileset) {
        $php .= "
" . $fileset->toPhp($context) . "
";
        //
        // In the following callback we assume that the fileset returns a
        // directory only *after* all it's content.
        //
        $php .= "
\$callback = function (\$entry) {
  \$ret = true;
  if (is_file(\$entry) || ({$this->getIncludeEmptyDirs()} && is_dir(\$entry))) { // includeemptydirs
    // TODO: activate the unlink() and unleash hell with extreme prejudice
    //\$ret = @unlink(\$entry);
  }
  if (!\$ret) {
    output(\"Failed deleting \$entry.\");
  } else {
    output(\"Deleted \$entry.\");
  }
  return \$ret;
};
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