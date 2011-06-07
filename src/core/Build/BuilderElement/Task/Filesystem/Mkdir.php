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
 * Mkdir task is responsible for creating and properly setting up new
 * directories.
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
class Build_BuilderElement_Task_Filesystem_Mkdir extends Build_BuilderElement
{
  protected $_dir;

  public function __construct()
  {
    parent::__construct();
    $this->_dir = null;
  }

  /**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    return new self();
  }

  public function toAnt()
  {
    if (!$this->getDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Dir not set for mkdir task.', __METHOD__);
      return false;
    }
    $xml = new XmlBuilderElement();
    $xml->startElement('mkdir');
    $xml->writeAttribute('dir', $this->getDir());
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
      $o->getHtmlTitle(array('title' => 'Mkdir'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        // Dir, textfield
        h::div(array('class' => 'label'), 'Dir');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => $o->getDir()));
        });
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
    if (!$this->getDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Dir not set for mkdir task.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['result']['task'] = 'mkdir';
\$getDir = expandStr('{$this->getDir()}');
if (!file_exists(\$getDir)) {
  if (mkdir(\$getDir, " . DEFAULT_DIR_MASK . ", true) === false && {$this->getFailOnError()}) {
    \$GLOBALS['result']['ok'] = false;
    output('Could not create ' . \$getDir . '.');
    return false;
  } else {
    \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
    output('Created ' . \$getDir . '.');
  }
} else {
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
  output(\$getDir . ' already exists.');
}
";
    return $php;
  }
}