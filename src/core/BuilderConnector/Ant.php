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
 * 
 */
class BuilderConnector_Ant
{
  static public function BuilderElement_Project(BuilderElement_Project $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('project');
    if (!$o->getTargets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No targets set for the project.', __METHOD__);
      return false;
    }
    if (!$o->getDefaultTarget()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No default target set for the project.', __METHOD__);
      return false;
    }
    if ($o->getBaseDir() !== null) {
      $xml->writeAttribute('basedir', $o->getBaseDir());
    }
    if ($o->getDefaultTarget() !== null) {
      $xml->writeAttribute('default', $o->getDefaultTarget());
    }
    if ($o->getName() !== null) {
      $xml->writeAttribute('name', $o->getName());
    }
    if ($o->getProperties()) {
      $properties = $o->getProperties();
      foreach ($properties as $property) {
        $xml->writeRaw(self::BuilderElement_Type_Property($property));
      }
    }
    if ($o->getTargets()) {
      $targets = $o->getTargets();
      foreach ($targets as $target) {
        $xml->writeRaw(self::BuilderElement_Target($target));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Target(BuilderElement_Target $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('target');
    if (!$o->getName()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No name set for the target.', __METHOD__);
      return false;
    }
    if ($o->getName() !== null) {
      $xml->writeAttribute('name', $o->getName());
    }
    if ($o->getProperties()) {
      $properties = $o->getProperties();
      foreach ($properties as $property) {
        $xml->writeRaw(self::BuilderElement_Type_Property($property));
      }
    }
    if ($o->getDependencies()) {
      $dependencies = $o->getDependencies();
      $value = '';
      for ($i=0; $i < count($dependencies); $i++) {
        if ($i > 0) {
          $value .= ',';
        }
        $value .= $dependencies[$i];
      }
      $xml->writeAttribute('depends', $value);
    }
    if ($o->getTasks()) {
      $tasks = $o->getTasks();
      foreach ($tasks as $task) {
        $method = get_class($task);
        $xml->writeRaw(self::$method($task));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Task_Filesystem_Chmod(BuilderElement_Task_Filesystem_Chmod $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('chmod');
    if (!$o->getFile() && !$o->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task chmod.', __METHOD__);
      return false;
    }
    $mode = $o->getMode();
    if (empty($mode) || !preg_match('/^\d{3}$/', $mode)) {
      SystemEvent::raise(SystemEvent::ERROR, 'No mode set for task chmod.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('perm', $mode);
    if ($o->getFile()) {
      $xml->writeAttribute('file', $o->getFile());
    } elseif ($o->getFilesets()) {
      $filesets = $o->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw(self::BuilderElement_Type_Fileset($fileset));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  /**
   * 
   * !! BuilderConnector_Phing has a direct dependency on this !!
   * 
   */
  static public function BuilderElement_Task_Filesystem_Chown(BuilderElement_Task_Filesystem_Chown $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('chown');
    if (!$o->getFile() && !$o->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task chown.', __METHOD__);
      return false;
    }
    if (!$o->getUser()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No user set for task chown.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('user', $o->getUser());
    if ($o->getFile()) {
      $xml->writeAttribute('file', $o->getFile());
    } elseif ($o->getFilesets()) {
      $filesets = $o->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw(self::BuilderElement_Type_Fileset($fileset));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Task_Filesystem_Delete(BuilderElement_Task_Filesystem_Delete $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('delete');
    if (!$o->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task delete.', __METHOD__);
      return false;
    }
    if ($o->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($o->getFailOnError()?'true':'false'));
    }
    if ($o->getIncludeEmptyDirs()) {
      $xml->writeAttribute('includeemptydirs', ($o->getIncludeEmptyDirs()?'true':'false'));
    }
    if ($o->getFilesets()) {
      $filesets = $o->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw(self::BuilderElement_Type_Fileset($fileset));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Task_Echo(BuilderElement_Task_Echo $o)
  {
    if (!$o->getMessage()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Message not set for echo task.', __METHOD__);
      return false;
    }
    $xml = new XmlBuilderElement();
    $xml->startElement('echo');
    if ($o->getFile()) {
      $xml->writeAttribute('file', $o->getFile());
      if ($o->getAppend() !== null) {
        $xml->writeAttribute('append', ($o->getAppend()?'true':'false'));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Task_Exec(BuilderElement_Task_Exec $o)
  {
    if (!$o->getExecutable()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Executable not set for exec task.', __METHOD__);
      return false;
    }
    $xml = new XmlBuilderElement();
    $xml->startElement('exec');
    if ($o->getOutputProperty()) {
      $xml->writeAttribute('outputproperty', $o->getOutputProperty());
    }
    if ($o->getBaseDir()) {
      $xml->writeAttribute('dir', $o->getBaseDir());
    }
    if ($o->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($o->getFailOnError()?'true':'false'));
    }
    $xml->writeAttribute('executable', $o->getExecutable());
    if ($o->getArgs()) {
      $args = $o->getArgs();
      foreach ($args as $arg) {
        $xml->startElement('arg');
        $xml->writeAttribute('line', $arg);
        $xml->endElement();
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Task_Filesystem_Mkdir(BuilderElement_Task_Filesystem_Mkdir $o)
  {
    if (!$o->getDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Dir not set for mkdir task.', __METHOD__);
      return false;
    }
    $xml = new XmlBuilderElement();
    $xml->startElement('mkdir');
    $xml->writeAttribute('dir', $o->getDir());
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Task_PhpLint(BuilderElement_Task_PhpLint $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('apply');
    if (!$o->getFilesets()) {
      SystemEvent::raise(SystemEvent::ERROR, 'No files set for task php lint.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('executable', 'php');
    if ($o->getFailOnError() !== null) {
      $xml->writeAttribute('failonerror', ($o->getFailOnError()?'true':'false'));
    }
    $xml->startElement('arg');
    $xml->writeAttribute('value', '-l');
    $xml->endElement();
    if ($o->getFilesets()) {
      $filesets = $o->getFilesets();
      foreach ($filesets as $fileset) {
        $xml->writeRaw(self::BuilderElement_Type_Fileset($fileset));
      }
    }
    $xml->endElement();
    return $xml->flush();
  }
  
  static public function BuilderElement_Type_Fileset(BuilderElement_Type_Fileset $o)
  {
    $xml = new XmlBuilderElement();
    
    $xml->startElement('fileset');
    if (!$o->getDir()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Root dir not set for type fileset.', __METHOD__);
      return false;
    }
    if ($o->getDir()) {
      $xml->writeAttribute('dir', $o->getDir());
    }
    if ($o->getDefaultExcludes() !== null) {
      $xml->writeAttribute('defaultexcludes', ($o->getDefaultExcludes()?'yes':'no'));
    }
    if ($o->getId()) {
      $xml->writeAttribute('id', $o->getId());
    }
    if ($o->getInclude()) {
      $includes = $o->getInclude();
      foreach ($includes as $include) {
        $xml->startElement('include');
        $xml->writeAttribute('name', $include);
        $xml->endElement();
      }
    }
    if ($o->getExclude()) {
      $excludes = $o->getExclude();
      foreach ($excludes as $exclude) {
        $xml->startElement('exclude');
        $xml->writeAttribute('name', $exclude);
        $xml->endElement();
      }
    }
    $xml->endElement();
    $ret = $xml->flush();
    $xml = null;
    unset($xml);
    return $ret;
  }
  
  static public function BuilderElement_Type_Property(BuilderElement_Type_Property $o)
  {
    $xml = new XmlBuilderElement();
    $xml->startElement('property');
    if (!$o->getName() || !$o->getValue()) {
      SystemEvent::raise(SystemEvent::ERROR, 'Name and value not set for type property.', __METHOD__);
      return false;
    }
    $xml->writeAttribute('name', $o->getName());
    $xml->writeAttribute('value', $o->getValue());
    $xml->endElement();
    return $xml->flush();
  }
}