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
 * Target builder element. It is responsible for aggregating properties
 * and tasks.
 *
 * @package     Build
 * @subpackage  BuilderElement
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Build_BuilderElement_Target extends Build_BuilderElement
{
  protected $_dependencies; // An array of target names that this target depends on
  protected $_name;
  protected $_properties;
  protected $_tasks;

  public function __construct()
  {
    parent::__construct();
    $this->_dependencies = array();
    $this->_properties = array();
    $this->_tasks = array();
  }

	/**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    return new self();
  }

  public function addTask($o)
  {
    $this->_tasks[] = $o;
  }

  public function addProperty($o)
  {
    $this->_properties[] = $o;
  }

  public function toAnt()
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('target');
    if (!$this->getName()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No name set for the target.', __METHOD__);
      return false;
    }
    if ($this->getName() !== null) {
      $xml->writeAttribute('name', $this->getName());
    }
    if ($this->getProperties()) {
      $properties = $this->getProperties();
      foreach ($properties as $property) {
        $xml->writeRaw($property->toAnt());
      }
    }
    if ($this->getDependencies()) {
      $dependencies = $this->getDependencies();
      $value = '';
      for ($i=0; $i < count($dependencies); $i++) {
        if ($i > 0) {
          $value .= ',';
        }
        $value .= $dependencies[$i];
      }
      $xml->writeAttribute('depends', $value);
    }
    if ($this->getTasks()) {
      $tasks = $this->getTasks();
      foreach ($tasks as $task) {
        $xml->writeRaw($task->toAnt());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  public function toHtml()
  {
    parent::toHtml();
    //
    // TODO: no support yet for targets, go straight for tasks within
    //
    if ($this->getTasks()) {
      $tasks = $this->getTasks();
      foreach ($tasks as $task) {
        $task->toHtml();
      }
    }
  }

  public function toPhing()
  {
    return $this->toAnt();
  }

  public function toPhp(Array &$context = array())
  {
    $php = '';
    if (!$this->getName()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No name set for the target.', __METHOD__);
      return false;
    }
    $targetName = "{$this->getName()}_{$context['id']}";
    $php .= <<<EOT
\$GLOBALS['targets']['{$targetName}'] = '{$targetName}';
EOT;
    if (!function_exists($targetName)) {
      $php .= <<<EOT
function {$targetName}() {
EOT;
      if ($this->getProperties()) {
        $properties = $this->getProperties();
        foreach ($properties as $property) {
          $php .= $property->toPhp($context);
        }
      }
      if ($this->getDependencies()) {
        $deps = $this->getDependencies();
        $php .= <<<EOT
  \$GLOBALS['targetDeps']['{$targetName}'] = array();
EOT;
        foreach ($deps as $dep) {
          $php .= <<<EOT
  \$GLOBALS['targetDeps']['{$targetName}'][] = '{$dep}';
EOT;
        }
      }
      if ($this->getTasks()) {
        $tasks = $this->getTasks();
        foreach ($tasks as $task) {
          $php .= $task->toPhp($context);
        }
      } else {
        //
        // TODO: Unfortunately will never get triggered while we have
        // properties mixed in with tasks (to simplify sorting and editing
        // in general)
        //
      $php .= <<<EOT
output("No tasks available to run.");
EOT;
    }
      $php .= <<<EOT
}
EOT;
    } else {
      $php .= "
// Function {$targetName}() already declared.
";
    }
    return $php;
  }
}