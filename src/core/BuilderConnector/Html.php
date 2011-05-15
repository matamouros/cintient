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

require_once 'lib/lib.htmlgen.php';

/**
 * 
 */
class BuilderConnector_Html
{
  /**
   * 
   * Helper function for centralizing all Build Element's title HTML
   * @param Array $params
   */
  static public function builderElementTitle(Array $params = array())
  {
    h::div(array('class' => 'builderElementTitle'), function() use ($params) {
      h::p(array('class' => 'title'), $params['title']);
      h::ul(array('class' => 'options'), function() {
        h::li(function() {h::a('save', '#');});
        h::li(function() {h::p(array('class' => 'pipe'), ' | ');});
        h::li(function() {h::a('x', '#');});
      });
    });
  }
  
  static public function BuilderElement_Project(BuilderElement_Project $o)
  {
    h::set_indent_pattern('  ');
    /*
    h::div(array('class' => 'builderElement'), function() use ($o) {
      h::div(array('class' => 'builderElementTitle'), 'Project');
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        h::form(array('id' => '', 'action' => ''), function() use ($o) {
          // Name, textfield
          h::div(array('class' => 'label'), 'Name');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'project', 'value' => $o->getName()));
          });
          // Basedir, textfield
          h::div(array('class' => 'label'), 'Basedir');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'basedir', 'value' => $o->getBaseDir()));
          });
          // Default target, textfield
          h::div(array('class' => 'label'), 'Default target');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'default', 'value' => $o->getDefaultTarget()));
          });
          // TODO: Properties, with support for "add more" automatically
          h::div(array('class' => 'label'), 'Basedir');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'basedir', 'value' => $o->getBaseDir()));
          });
        });
      });
    });*/
    h::form(array('id' => '', 'action' => ''), function() use ($o) {
      if ($o->getTargets()) {
        $targets = $o->getTargets();
        foreach ($targets as $target) {
          // self:: doesn't work here...
          BuilderConnector_Html::BuilderElement_Target($target);
        }
      }
      h::input(array('type' => 'submit', 'value' => 'Save all!', 'id' => 'submitButton'));
    });
  }
  
  static public function BuilderElement_Target(BuilderElement_Target $o)
  {
    //
    // TODO: no support yet for targets, go straight for tasks within
    //
    if ($o->getTasks()) {
      $tasks = $o->getTasks();
      foreach ($tasks as $task) {
        $method = get_class($task);
        self::$method($task);
      }
    }
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
    h::div(array('class' => 'builderElement'), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Echo'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        h::form(array('id' => '', 'action' => ''), function() use ($o) {
          // Message, textfield
          h::div(array('class' => 'label'), 'Message');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'message', 'value' => $o->getMessage()));
          });
          // File, textfield
          h::div(array('class' => 'label'), 'File');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'file', 'value' => $o->getFile()));
          });
          // Append, checkbox
          h::div(array('class' => 'label'), 'Append?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'append', 'selected' => ($o->getAppend()?'selected':'')));
          });
        });
      });
    });
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
    h::div(array('class' => 'builderElement'), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'PhpLint'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        h::form(array('id' => '', 'action' => ''), function() use ($o) {
          // Fail on error, checkbox
          h::div(array('class' => 'label'), 'Fail on error?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failonerror', 'selected' => ($o->getFailOnError()?'selected':'')));
          });
          if ($o->getFilesets()) {
            $filesets = $o->getFilesets();
            foreach ($filesets as $fileset) {
              // self:: doesn't work here...
              BuilderConnector_Html::BuilderElement_Type_Fileset($fileset);
            }
          }
          // TODO: Add HTML button for adding new fileset.
        });
      });
    });
  }
  
  static public function BuilderElement_Task_PhpUnit(BuilderElement_Task_PhpUnit $o)
  {
    h::div(array('class' => 'builderElement'), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'PhpUnit'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        h::form(array('id' => '', 'action' => ''), function() use ($o) {
          // Fail on error, checkbox
          h::div(array('class' => 'label'), 'Fail on error?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failonerror', 'selected' => ($o->getFailOnError()?'selected':'')));
          });
          // Fail on failure, checkbox
          h::div(array('class' => 'label'), 'Fail on failure?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failonfailure', 'selected' => ($o->getFailOnFailure()?'selected':'')));
          });
          // Fail on incomplete, checkbox
          h::div(array('class' => 'label'), 'Fail on incomplete?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failonincomplete', 'selected' => ($o->getFailOnIncomplete()?'selected':'')));
          });
          // Fail on skipped, checkbox
          h::div(array('class' => 'label'), 'Fail on skipped?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failonskipped', 'selected' => ($o->getFailOnSkipped()?'selected':'')));
          });
          if ($o->getFilesets()) {
            $filesets = $o->getFilesets();
            foreach ($filesets as $fileset) {
              // self:: doesn't work here...
              BuilderConnector_Html::BuilderElement_Type_Fileset($fileset);
            }
          }
          // TODO: Add HTML button for adding new fileset.
        });
      });
    });
  }
  
  static public function BuilderElement_Type_Fileset(BuilderElement_Type_Fileset $o)
  {
    //h::div(array('class' => 'builderElement'), function() use ($o) {
    //  h::div(array('class' => 'builderElementTitle'), 'Fileset');
    //  h::div(array('class' => 'builderElementForm'), function() use ($o) {
    //    h::form(array('id' => '', 'action' => ''), function() use ($o) {
          // Default excludes, checkbox
          h::div(array('class' => 'label'), 'Default excludes?');
          h::div(array('class' => 'checkboxContainer'), function() use ($o) {
            h::input(array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'defaultexcludes', 'selected' => ($o->getDefaultExcludes()?'selected':'')));
          });
          // Dir, textfield
          h::div(array('class' => 'label'), 'Dir');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => $o->getDir()));
          });
          // Id, textfield
          /*h::div(array('class' => 'label'), 'Id');
          h::div(array('class' => 'textfieldContainer'), function() use ($o) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'id', 'value' => $o->getId()));
          });*/
          $includesLine = '';
          if ($o->getInclude()) {
            $includes = $o->getInclude();
            foreach ($includes as $include) {
              $includesLine .= $include . ', ';
            }
            // TODO: Oh god... Seriously do this better:
            if (!empty($includesLine)) {
              $includesLine = substr($includesLine, 0, strlen($includesLine)-2); // Oh god...
            }
          }
          // Includes, textfield
          h::div(array('class' => 'label'), 'Includes');
          h::div(array('class' => 'textfieldContainer'), function() use ($o, $includesLine) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'includes', 'value' => $includesLine));
          });
          $excludesLine = '';
          if ($o->getExclude()) {
            $excludes = $o->getExclude();
            foreach ($excludes as $exclude) {
              $excludesLine .= $exclude . ', ';
            }
            // TODO: Oh god... Seriously do this better:
            if (!empty($excludesLine)) {
              $excludesLine = substr($excludesLine, 0, strlen($excludesLine)-2); // Oh god...
            }
          }
          // Excludes, textfield
          h::div(array('class' => 'label'), 'Excludes');
          h::div(array('class' => 'textfieldContainer'), function() use ($o, $excludesLine) {
            h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'excludes', 'value' => $excludesLine));
          });
    //    });
    //  });
    //});
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