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
 * The exec task is perhaps the single most universal and important task.
 * All other tasks could be, in some way or another be specified by an
 * exec task.
 *
 * Usage:
 *
 * $exec = new Build_BuilderElement_Task_Exec();
 * $exec->setExecutable('php');
 * $exec->setArgs('runMe.php arg1 arg2');
 * $exec->setDir('/tmp/');
 * $exec->setOutputProperty('fooBar');
 * echo $exec->toString('ant');
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
class Build_BuilderElement_Task_Exec extends Build_BuilderElement
{
  protected $_executable;
  protected $_args;            // The arguments to the executable command, if any, a space separated string
  protected $_baseDir;         // The directory in which the command should be executed in
  protected $_outputProperty;  // Log the command's output to the variable with this name

  public function __construct()
  {
    parent::__construct();
    $this->_executable = null;
    $this->_args = null;
    $this->_baseDir = null;
    $this->_outputProperty = null;
  }

	/**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    return new self();
  }

	/**
   * Setter. Makes sure <code>$dir</code> always ends in a valid
   * <code>DIRECTORY_SEPARATOR</code> token.
   *
   * @param string $dir
   */
  public function setBaseDir($dir)
  {
    if (!empty($dir) && strpos($dir, DIRECTORY_SEPARATOR, (strlen($dir)-1)) === false) {
      $dir .= DIRECTORY_SEPARATOR;
    }
    $this->_baseDir = $dir;
  }

  public function toAnt()
  {
    if (!$this->getExecutable()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Executable not set for exec task.', __METHOD__);
      return false;
    }
    $xml = new Build_XmlBuilderElement();
    $xml->startElement('exec');
    if ($this->getOutputProperty()) {
      $xml->writeAttribute('outputproperty', $this->getOutputProperty());
    }
    if ($this->getBaseDir()) {
      $xml->writeAttribute('dir', $this->getBaseDir());
    }
    if ($this->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($this->getFailOnError()?'true':'false'));
    }
    $xml->writeAttribute('executable', $this->getExecutable());
    if ($this->getArgs()) {
      $args = $this->getArgs();
      foreach ($args as $arg) {
        $xml->startElement('arg');
        $xml->writeAttribute('line', $arg);
        $xml->endElement();
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
      $o->getHtmlTitle(array('title' => 'Exec'));
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
        // Executable, textfield
        h::div(array('class' => 'label'), 'Executable');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'executable', 'value' => $o->getExecutable()));
        });
        // Args, textfield
        h::div(array('class' => 'label'), 'Args <span class="fineprintLabel">(space separated)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'args', 'value' => $o->getArgs()));
        });
        // Dir, textfield
        h::div(array('class' => 'label'), 'Base dir');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'basedir', 'value' => $o->getBaseDir()));
        });
        // Output property, textfield
        h::div(array('class' => 'label'), 'Output property');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'outputProperty', 'value' => $o->getOutputProperty()));
        });
      });
    });
  }

  public function toPhing()
  {
    if (!$this->getExecutable()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Executable not set for exec task.', __METHOD__);
      return false;
    }
    $xml = new Build_XmlBuilderElement();
    $xml->startElement('exec');
    if ($this->getOutputProperty()) {
      $xml->writeAttribute('outputProperty', $this->getOutputProperty());
    }
    if ($this->getBaseDir()) {
      $xml->writeAttribute('dir', $this->getBaseDir());
    }
    $args = '';
    if ($this->getArgs()) {
      $args = ' ' . implode(' ', $this->getArgs());
    }
    $xml->writeAttribute('command', $this->getExecutable() . $args);
    $xml->endElement();
    return $xml->flush();
  }

  public function toPhp(Array &$context = array())
  {
    $php = '';
    if (!$this->getExecutable()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Executable not set for exec task.', __METHOD__);
      return false;
    }
    $php .= "
\$GLOBALS['result']['task'] = 'exec';
\$getBaseDir = '';
";
    if ($this->getBaseDir()) {
      $php .= "
\$getBaseDir = \"cd \" . expandStr('{$this->getBaseDir()}') . \"; \";
";
    }
    $php .= "
\$args = '';
";
    if ($this->getArgs()) {
      $php .= "
\$getArgs = expandStr(' {$this->getArgs()}');
";
    }
    $php .= "
\$getExecutable = expandStr('{$this->getExecutable()}');
\$GLOBALS['result']['task'] = 'exec';
output(\"Executing '\$getBaseDir\$getExecutable\$getArgs'.\");
\$ret = exec(\"\$getBaseDir\$getExecutable\$getArgs\", \$lines, \$retval);
foreach (\$lines as \$line) {
  output(\$line);
}
";
    if ($this->getOutputProperty()) {
      $php .= "
\$GLOBALS['properties']['{$this->getOutputProperty()}_{$context['id']}'] = \$ret;
";
    }
    $php .= "
if (\$retval > 0) {
  output('Failed.');
  if ({$this->getFailOnError()}) {
    \$GLOBALS['result']['ok'] = false;
    return false;
  } else {
    \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
  }
} else {
  \$GLOBALS['result']['ok'] = \$GLOBALS['result']['ok'] & true;
  output('Success.');
}
";
    //TODO: bullet proof this for boolean falses (they're not showing up)
    /*
    $php .= "if ({$this->getFailOnError()} && !\$ret) {
  \$GLOBALS['result']['ok'] = false;
  return false;
}
\$GLOBALS['result']['ok'] = true;
return true;
";*/
    return $php;
  }
}