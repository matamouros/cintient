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
 * The decision was made to fork this connector and make a specific one
 * for Cintient integration builders. This was initially required because
 * the integration builder required a continuous run, and the PHP
 * connector only allowed a one-time execution of the exported builder.
 * Since then I've decided to bring back these changes into the PHP
 * connector - thus at first glance rendering the initial fork useless -
 * but this doesn't mean that in the very near future the two connectors
 * won't have to go again separate ways. They are two logically different
 * connectors, and only temporarily and extraordinarilly are implemented
 * exactly the same way.
 *
 * What to consider:
 *  $GLOBALS['filesets'][<ID>]                      Holds all filesets
 *  $GLOBALS['filesets'][<ID>]['dir']               The fileset root dir
 *  $GLOBALS['filesets'][<ID>]['defaultExcludes']   Holds default exclusions (optional, default is use them)
 *  $GLOBALS['filesets'][<ID>]['exclude']           Holds files/dirs to exclude
 *  $GLOBALS['filesets'][<ID>]['include']           Holds files/dirs to include
 *  $GLOBALS['id']                     Holds an ID of the current builder
 *  $GLOBALS['properties'][]           Holds global project properties
 *  $GLOBALS['properties'][<TASK>][]   Holds local task related properties
 *  $GLOBALS['result']['ok']           Holds the success or failure of last task's execution
 *  $GLOBALS['result']['output']       Holds the output of the last task's execution, if any
 *  $GLOBALS['result']['stacktrace']   The stacktrace of the error
 *  $GLOBALS['result']['task']         Holds the last task executed
 *  $GLOBALS['targets'][]              0-index based array with all the targets in their actual execution order
 *  $GLOBALS['targetsDefault']         Holds the name of the default target to execute
 *  $GLOBALS['targetsDeps'][<ID>][]    Holds the names of the target's dependency targets
 *
 * @package Builder
 */
class BuilderConnector_Php
{
  /**
   *
   * Mandatory attributes:
   * . targets
   * . defaultTarget
   *
   * @param BuilderElement_Project $o
   */
  static public function BuilderElement_Project(BuilderElement_Project $o)
  {
    $php = '';
    $context = array();
    $context['id'] = $o->getInternalId();
    if (empty($context['id'])) {
      SystemEvent::raise(SystemEvent::ERROR, 'A unique identifier for the project is required.', __METHOD__);
      return false;
    }
    if (!$o->getTargets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No targets set for the project.', __METHOD__);
      return false;
    }
    if (!$o->getDefaultTarget()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No default target set for the project.', __METHOD__);
      return false;
    }
    //
    // TODO: uncomment this in production
    //
    $php .= "
//error_reporting(0);
";
    if ($o->getBaseDir() !== null) {
      $php .= <<<EOT
set_include_path(get_include_path() . PATH_SEPARATOR . {$o->getBaseDir()});
EOT;
    }
    if ($o->getDefaultTarget() !== null) {
      $php .= <<<EOT
\$GLOBALS['targets'] = array();
\$GLOBALS['targetDefault'] = '{$o->getDefaultTarget()}_{$context['id']}';
\$GLOBALS['result'] = array();
\$GLOBALS['result']['ok'] = false;
\$GLOBALS['result']['output'] = '';
\$GLOBALS['result']['stacktrace'] = array();
EOT;
      //
      // The following because the internal cron emulation process runs
      // without exiting, and the second time around will redeclare
      // ilegally this function.
      //
      if (!function_exists('output')) {
        $php .= <<<EOT
function output(\$task, \$message)
{
  \$GLOBALS['result']['stacktrace'][] = "[" . date('H:i:s') . "] [{\$task}] {\$message}";
}
EOT;
      }
    }
    $properties = $o->getProperties();
    if ($o->getProperties()) {
      foreach ($properties as $property) {
        $php .= self::BuilderElement_Type_Property($property, $context);
      }
    }
    $targets = $o->getTargets();
    if ($o->getTargets()) {
      foreach ($targets as $target) {
        $php .= self::BuilderElement_Target($target, $context);
      }
    }
    $php .= <<<EOT
foreach (\$GLOBALS['targets'] as \$target) {
  output('target', "Executing target \"\$target\"...");
  if (\$target() === false) {
    \$error = error_get_last();
    \$GLOBALS['result']['output'] = \$error['message'] . ', on line ' . \$error['line'] . '.';
    output('target', "Target \"\$target\" failed.");
    return false;
  } else {
    output('target', "Target \"\$target\" executed.");
  }
}
EOT;
    return $php;
  }

  static public function BuilderElement_Target(BuilderElement_Target $o, array &$context = array())
  {
    $php = '';
    if (!$o->getName()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No name set for the target.', __METHOD__);
      return false;
    }
    $targetName = "{$o->getName()}_{$context['id']}";
    $php .= <<<EOT
\$GLOBALS['targets']['{$targetName}'] = '{$targetName}';
EOT;
    if (!function_exists($targetName)) {
      $php .= <<<EOT
function {$targetName}() {
EOT;
      if ($o->getProperties()) {
        $properties = $o->getProperties();
        foreach ($properties as $property) {
          $php .= self::BuilderElement_Type_Property($property, $context);
        }
      }
      if ($o->getDependencies()) {
        $deps = $o->getDependencies();
        $php .= <<<EOT
  \$GLOBALS['targetDeps']['{$targetName}'] = array();
EOT;
        foreach ($deps as $dep) {
          $php .= <<<EOT
  \$GLOBALS['targetDeps']['{$targetName}'][] = '{$dep}';
EOT;
        }
      }
      if ($o->getTasks()) {
        $tasks = $o->getTasks();
        foreach ($tasks as $task) {
          $method = get_class($task);
          $taskOutput = self::$method($task, $context);
          $php .= <<<EOT
  {$taskOutput}
EOT;
        }
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

  static public function BuilderElement_Task_Filesystem_Chmod(BuilderElement_Task_Filesystem_Chmod $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Filesystem_Chmod($o, $context);
  }

  static public function BuilderElement_Task_Filesystem_Chown(BuilderElement_Task_Filesystem_Chown $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Filesystem_Chown($o, $context);
  }

  static public function BuilderElement_Task_Filesystem_Copy(BuilderElement_Task_Filesystem_Copy $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Filesystem_Copy($o, $context);
  }

  static public function BuilderElement_Task_Filesystem_Delete(BuilderElement_Task_Filesystem_Delete $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Filesystem_Delete($o, $context);
  }

  static public function BuilderElement_Task_Echo(BuilderElement_Task_Echo $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Echo($o, $context);
  }

  static public function BuilderElement_Task_Exec(BuilderElement_Task_Exec $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Exec($o, $context);
  }

  static public function BuilderElement_Task_Filesystem_Mkdir(BuilderElement_Task_Filesystem_Mkdir $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_Filesystem_Mkdir($o, $context);
  }

  static public function BuilderElement_Task_PhpLint(BuilderElement_Task_PhpLint $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_PhpLint($o, $context);
  }

  static public function BuilderElement_Task_PhpUnit(BuilderElement_Task_PhpUnit $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Task_PhpUnit($o, $context);
  }

  /**
   * Loosely based on phing's SelectorUtils::matchPath.
   *
   * @param BuilderElement_Type_Fileset $o
   */
  static public function BuilderElement_Type_Fileset(BuilderElement_Type_Fileset $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Type_Fileset($o, $context);
  }

  static public function BuilderElement_Type_Properties(BuilderElement_Type_Properties $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Type_Properties($o, $context);
  }

  static public function BuilderElement_Type_Property(BuilderElement_Type_Property $o, array &$context = array())
  {
    return BuilderConnector_Cintient::BuilderElement_Type_Property($o, $context);
  }

  static public function execute($code)
  {
    return BuilderConnector_Cintient::execute($code);
  }
}