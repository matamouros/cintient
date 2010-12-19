<?php
/*
 * 
 * Cintient, Continuous Integration made simple.
 * Copyright (c) 2011, Pedro Mata-Mouros Fonseca
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * . Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * . Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 *   
 * . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
 *   the names of its contributors may be used to endorse or promote
 *   products derived from this software without specific prior
 *   written permission.
 *   
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
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
  
  static public function BuilderElement_Task_Delete(BuilderElement_Task_Delete $o)
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
    if ($o->getDir()) {
      $xml->writeAttribute('dir', $o->getDir());
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
  
  static public function BuilderElement_Task_Mkdir(BuilderElement_Task_Mkdir $o)
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