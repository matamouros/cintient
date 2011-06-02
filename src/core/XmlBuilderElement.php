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
 * @package Builder
 */
class XmlBuilderElement
{
  private $_xml;

  public function __construct()
  {
    $this->_xml = new XMLWriter();
    $this->_xml->openMemory();
    //$this->_xml->startDocument(); // this hack avoids the output of the <?xml version="1.0" element
    $this->_xml->setIndent(true);
    $this->_xml->setIndentString('  ');
  }

  public function __destruct()
  {
    $this->_xml = null;
    unset($this->_xml);
  }

  public function __call($name, $args)
  {
    if (method_exists($this->_xml, $name)) {
      if (empty($args)) {
        return $this->_xml->$name();
      } elseif (count($args) == 1) {
        return $this->_xml->$name($args[0]);
      } elseif (count($args) == 2) {
        return $this->_xml->$name($args[0], $args[1]);
      } else {
        trigger_error("Invalid arguments specified for called method", E_USER_ERROR);
        exit;
      }
    } else {
      trigger_error("No valid method available for calling", E_USER_ERROR);
      exit;
    }
  }

  public function flush()
  {
    $this->_xml->endDocument();
    return $this->_xml->flush();
  }
}