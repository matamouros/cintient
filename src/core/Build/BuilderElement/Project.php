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
 * Project builder element. This is the top level builder element, under
 * which all others reside.
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
class Build_BuilderElement_Project extends Build_BuilderElement
{
  protected $_baseDir;
  protected $_defaultTarget;
  protected $_name;
  protected $_properties; // An array with references to Property objects
  protected $_targets;    // An array with references to all target objects

  public function __construct()
  {
    parent::__construct();
    $this->_baseDir = null;
    $this->_defaultTarget = null;
    $this->_name = null;
    $this->_properties = array();
    $this->_targets = array();
  }

	/**
   * Creates a new instance of this builder element, with default values.
   */
  static public function create()
  {
    return new self();
  }

  public function addProperty(Build_BuilderElement_Type_Property $o)
  {
    $this->_properties[] = $o;
  }

  public function addTarget(Build_BuilderElement_Target $o)
  {
    $this->_targets[] = $o;
  }

  public function isEmpty()
  {
    return empty($this->_targets);
  }

  public function toAnt()
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('project');
    if (!$this->getTargets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No targets set for the project.', __METHOD__);
      return false;
    }
    if (!$this->getDefaultTarget()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No default target set for the project.', __METHOD__);
      return false;
    }
    if ($this->getBaseDir() !== null) {
      $xml->writeAttribute('basedir', $this->getBaseDir());
    }
    if ($this->getDefaultTarget() !== null) {
      $xml->writeAttribute('default', $this->getDefaultTarget());
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
    if ($this->getTargets()) {
      $targets = $this->getTargets();
      foreach ($targets as $target) {
        $xml->writeRaw($target->toAnt());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  public function toHtml()
  {
    parent::toHtml();
    h::set_indent_pattern('  ');
    $o = $this;
    /*
    h::div(array('class' => 'builderElement'), function() use ($o) {
      h::div(array('class' => 'builderElementTitle'), 'Project');
      h::div(array('class' => 'builderElementForm'), function() {
        h::form(array('id' => '', 'action' => UrlManager::getForAjaxProjectIntegrationBuilderSaveElement()), function() use ($o) {
          // Name, textfield
          h::div(array('class' => 'label'), 'Name');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'project', 'value' => $o->getName()));
          });
          // Basedir, textfield
          h::div(array('class' => 'label'), 'Basedir');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'baseDir', 'value' => $o->getBaseDir()));
          });
          // Default target, textfield
          h::div(array('class' => 'label'), 'Default target');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'defaultTarget', 'value' => $o->getDefaultTarget()));
          });
          // TODO: Properties, with support for "add more" automatically
          h::div(array('class' => 'label'), 'Basedir');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'baseDir', 'value' => $o->getBaseDir()));
          });
        });
      });
    });*/
    //h::form(array('id' => '', 'action' => UrlManager::getForAjaxProjectIntegrationBuilderSaveElement()), function() use ($o) {
      if ($o->getTargets()) {
        $targets = $o->getTargets();
        foreach ($targets as $target) {
          $target->toHtml();
        }
      }
      //h::input(array('type' => 'submit', 'value' => 'Save all!', 'id' => 'submitButton'));
    //});
  }

  public function toPhing()
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('project');
    if (!$this->getTargets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No targets set for the project.', __METHOD__);
      return false;
    }
    if (!$this->getBaseDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No basedir set for the project.', __METHOD__);
      return false;
    }
    if ($this->getBaseDir() !== null) {
      $xml->writeAttribute('basedir', $this->getBaseDir());
    }
    if ($this->getDefaultTarget() !== null) {
      $xml->writeAttribute('default', $this->getDefaultTarget());
    }
    if ($this->getName() !== null) {
      $xml->writeAttribute('name', $this->getName());
    }
    if ($this->getProperties()) {
      $properties = $this->getProperties();
      foreach ($properties as $property) {
        $xml->writeRaw($property->toPhing());
      }
    }
    if ($this->getTargets()) {
      $targets = $this->getTargets();
      foreach ($targets as $target) {
        $xml->writeRaw($target->toPhing());
      }
    }
    $xml->endElement();
    return $xml->flush();
  }

  /**
   * The Project builder element is the top level container for a builder,
   * and on the Php connector it is responsible for setting up a whole
   * host of variables that will be set throughout the whole builder
   * script's execution. These variables should then be checked by the
   * script execution caller's environment.
   *
   * The following are available within the builder, and afterwards on
   * the callers scope:
   *
   *  $GLOBALS['filesets'][<ID>]                    Holds all filesets
   *  $GLOBALS['filesets'][<ID>]['dir']             The fileset root dir
   *  $GLOBALS['filesets'][<ID>]['defaultExcludes'] Holds default exclusions (optional, default is use them)
   *  $GLOBALS['filesets'][<ID>]['exclude']         Holds files/dirs to exclude
   *  $GLOBALS['filesets'][<ID>]['include']         Holds files/dirs to include
   *  $GLOBALS['id']                                Holds an ID of the current builder
   *  $GLOBALS['properties'][]                      Holds global project properties
   *  $GLOBALS['properties'][<TASK>][]              Holds local task related properties
   *  $GLOBALS['result']['ok']                      Holds the success or failure of last task's execution
   *  $GLOBALS['result']['output']                  Holds the output of the last task's execution, if any
   *  $GLOBALS['result']['stacktrace']              The stacktrace of the error
   *  $GLOBALS['result']['task']                    Holds the last task executed
   *  $GLOBALS['targets'][]                         0-index based array with all the targets in their actual execution order
   *  $GLOBALS['targetsDefault']                    Holds the name of the default target to execute
   *  $GLOBALS['targetsDeps'][<ID>][]               Holds the names of the target's dependency targets
   */
  public function toPhp()
  {
    $php = '';
    $context = array();
    $context['id'] = $this->getInternalId();
    $context['properties'] = array(); // User properties might be needed at builder code generation time (see the copy task, for instance)
    if (empty($context['id'])) {
      SystemEvent::raise(SystemEvent::ERROR, 'A unique identifier for the project is required.', __METHOD__);
      return false;
    }
    if (!$this->getTargets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No targets set for the project.', __METHOD__);
      return false;
    }
    if (!$this->getDefaultTarget()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No default target set for the project.', __METHOD__);
      return false;
    }
    //
    // TODO: uncomment this in production
    //
    $php .= "
<?php
//error_reporting(0);
// Allow as much memory as possible by default
ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);
ini_set('display_errors', 'stderr');
// Define the CLI specific stream constants, that are not availble in
// the web server versions. PHP_Depend for instance relies on the STDERR
// one; don't know if any other also.
if (!defined('STDIN')) {
	define('STDIN', @fopen('php://stdin', 'r'));
}
if (!defined('STDOUT')) {
	define('STDOUT', @fopen('php://stdout', 'w'));
}
if (!defined('STDERR')) {
	define('STDERR', @fopen('php://stderr', 'w'));
}
stream_set_blocking(STDERR, false); // Don't let stderr block, it's only a 16KB buffer
set_include_path(get_include_path() . PATH_SEPARATOR . '" . CINTIENT_INSTALL_DIR . "lib/');
set_include_path(get_include_path() . PATH_SEPARATOR . '" . CINTIENT_INSTALL_DIR . "lib/PEAR/');
set_include_path(get_include_path() . PATH_SEPARATOR . '" . CINTIENT_INSTALL_DIR . "lib/PEAR/PHP/');
";
    if ($this->getBaseDir() !== null) {
      $php .= "
set_include_path(get_include_path() . PATH_SEPARATOR . '{$this->getBaseDir()}');
";
    }
    if ($this->getDefaultTarget() !== null) {
      $php .= <<<EOT
\$GLOBALS['targets'] = array();
\$GLOBALS['targetDefault'] = '{$this->getDefaultTarget()}_{$context['id']}';
\$GLOBALS['result'] = array();
\$GLOBALS['result']['ok'] = true;
\$GLOBALS['result']['output'] = '';
\$GLOBALS['result']['stacktrace'] = '';
\$GLOBALS['result']['task'] = null;
EOT;
      //
      // The following because the internal cron emulation process runs
      // without exiting, and the second time around will redeclare
      // ilegally this function.
      //
      if (!function_exists('output')) {
        $php .= <<<EOT
function output(\$message)
{
  \$GLOBALS['result']['stacktrace'] .= "[" . date('H:i:s') . "] [{\$GLOBALS['result']['task']}] {\$message}\n";
}
function expandStr(\$str)
{
  return preg_replace_callback('/\\$\{(\w*)\}/', function(\$matches) {
  	\$key = \$matches[1] . '_{$context['id']}';
    if (isset(\$GLOBALS['properties'][\$key])) {
      return \$GLOBALS['properties'][\$key];
    } else {
      output("Couldn't expand user variable {\$matches[0]}, no such property was found. Assumed value '{\$matches[1]}'.");
      return \$matches[1];
    }
  }, \$str);
}
EOT;
      }
    }
    $properties = $this->getProperties();
    if ($this->getProperties()) {
      foreach ($properties as $property) {
        $php .= $property->toPhp($context);
      }
    }
    $targets = $this->getTargets();
    if ($this->getTargets()) {
      foreach ($targets as $target) {
        $php .= $target->toPhp($context);
      }
    }
    $php .= "
foreach (\$GLOBALS['targets'] as \$target) {
  \$GLOBALS['result']['task'] = 'target';
  output(\"Executing target \$target...\");
  if (\$target() === false) {
    \$GLOBALS['result']['task'] = 'target';
    \$error = error_get_last();
    \$GLOBALS['result']['output'] = \$error['message'] . ', on line ' . \$error['line'] . '.';
    output(\"Target \$target failed.\");
    return false;
  } else {
    \$GLOBALS['result']['task'] = 'target';
    output(\"Target \$target executed.\");
  }
}
// Output globals result vars
foreach (\$GLOBALS['result'] as \$key => \$value) {
  \$value = str_replace(PHP_EOL, '" . CINTIENT_NEWLINE_TOKEN . "', \$value);
  fwrite(STDOUT, \"\$key=\$value\\n\");
}
@fclose(STDOUT);
@fclose(STDERR);
exit;
";
    return $php;
  }
}