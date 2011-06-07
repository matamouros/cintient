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
 * The property element is responsible for setting up a local (if defined
 * within a target) or global (if defined at the project level) variable
 * for easy reference in the builder script's scope.
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
class Build_BuilderElement_Type_Property extends Build_BuilderElement
{
  protected $_name;
  protected $_value;

  public function __construct()
  {
    parent::__construct();
    $this->_name = null;
    $this->_value = null;
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
    $xml = new XmlBuilderElement();
    $xml->startElement('property');
    if (!$this->getName() || !$this->getValue()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Name and value not set for type property.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('name', $this->getName());
    $xml->writeAttribute('value', $this->getValue());
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
      $o->getHtmlTitle(array('title' => 'Property'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        // Name, textfield
        h::div(array('class' => 'label'), 'Name');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'name', 'value' => $o->getName()));
        });
        // Value, textfield
        h::div(array('class' => 'label'), 'Value');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'value', 'value' => $o->getValue()));
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
    if (!$this->getName() || !$this->getValue()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Name and value not set for type property.', __METHOD__);
      return false;
    }
    $context['properties'][self::_expandStr($this->getName(), $context)] = self::_expandStr($this->getValue(), $context);
    $php .= <<<EOT
\$GLOBALS['properties'][expandStr('{$this->getName()}') . '_{$context['id']}'] = expandStr('{$this->getValue()}');
\$GLOBALS['result']['ok'] = (\$GLOBALS['result']['ok'] & true);
EOT;
    return $php;
  }
}