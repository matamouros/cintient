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
        h::li(function() {h::a('save', '#', array('class' => 'submit'));});
        h::li(function() {h::p(array('class' => 'pipe'), ' | ');});
        h::li(function() {h::a('x', '#', array('class' => 'delete'));});
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
          // self:: doesn't work here...
          BuilderConnector_Html::BuilderElement_Target($target);
        }
      }
      //h::input(array('type' => 'submit', 'value' => 'Save all!', 'id' => 'submitButton'));
    //});
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
  
  static public function BuilderElement_Task_Filesystem_Chmod(BuilderElement_Task_Filesystem_Chmod $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Chmod'));
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
        /*
        // File, textfield
        h::div(array('class' => 'label'), 'File');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'file', 'value' => $o->getFile()));
        });*/
        // Mode, textfield
        h::div(array('class' => 'label'), 'Mode <span class="fineprintLabel">(e.g., 755, 644, 640, etc)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'mode', 'value' => $o->getMode()));
        });
        // Filesets
        if ($o->getFilesets()) {
          $filesets = $o->getFilesets();
          foreach ($filesets as $fileset) {
            // self:: doesn't work here...
            BuilderConnector_Html::BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }
  
  static public function BuilderElement_Task_Filesystem_Chown(BuilderElement_Task_Filesystem_Chown $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Chown'));
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
        /*
        // File, textfield
        h::div(array('class' => 'label'), 'File');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'file', 'value' => $o->getFile()));
        });*/
        // User, textfield
        h::div(array('class' => 'label'), 'User <span class="fineprintLabel">(or user.group)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'user', 'value' => $o->getUser()));
        });
        // Filesets
        if ($o->getFilesets()) {
          $filesets = $o->getFilesets();
          foreach ($filesets as $fileset) {
            // self:: doesn't work here...
            BuilderConnector_Html::BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }
  
  static public function BuilderElement_Task_Filesystem_Delete(BuilderElement_Task_Filesystem_Delete $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Delete'));
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
        // Fail on error, checkbox
        h::div(array('class' => 'label'), 'Include empty dirs?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'includeEmptyDirs',);
          if ($o->getIncludeEmptyDirs()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Filesets
        if ($o->getFilesets()) {
          $filesets = $o->getFilesets();
          foreach ($filesets as $fileset) {
            // self:: doesn't work here...
            BuilderConnector_Html::BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }
  
  static public function BuilderElement_Task_Echo(BuilderElement_Task_Echo $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Echo'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
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
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'append',);
          if ($o->getAppend()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
      });
    });
  }
  
  static public function BuilderElement_Task_Exec(BuilderElement_Task_Exec $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Exec'));
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
  
  static public function BuilderElement_Task_Filesystem_Mkdir(BuilderElement_Task_Filesystem_Mkdir $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Mkdir'));
      h::div(array('class' => 'builderElementForm'), function() use ($o) {
        // Dir, textfield
        h::div(array('class' => 'label'), 'Dir');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => $o->getDir()));
        });
      });
    });
  }
  
  static public function BuilderElement_Task_PhpLint(BuilderElement_Task_PhpLint $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'PhpLint'));
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
  }
  
  static public function BuilderElement_Task_PhpUnit(BuilderElement_Task_PhpUnit $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'PhpUnit'));
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
        // Fail on failure, checkbox
        h::div(array('class' => 'label'), 'Fail on failure?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnFailure',);
          if ($o->getFailOnFailure()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Fail on incomplete, checkbox
        h::div(array('class' => 'label'), 'Fail on incomplete?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnIncomplete',);
          if ($o->getFailOnIncomplete()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Fail on skipped, checkbox
        h::div(array('class' => 'label'), 'Fail on skipped?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnSkipped',);
          if ($o->getFailOnSkipped()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
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
  }
  
  static public function BuilderElement_Type_Fileset(BuilderElement_Type_Fileset $o, BuilderElement $parent = null)
  {
    h::hr();
    if (empty($parent)) {
    }
    // Type, radio button
    h::ul(array('class' => 'radioContainer'), function() use ($o) {
      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Files only');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => BuilderElement_Type_Fileset::FILE);
        if ($o->getType() == BuilderElement_Type_Fileset::FILE) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });
      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Dirs only');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => BuilderElement_Type_Fileset::DIR);
        if ($o->getType() == BuilderElement_Type_Fileset::DIR) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });

      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Both');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => BuilderElement_Type_Fileset::BOTH);
        if ($o->getType() == BuilderElement_Type_Fileset::BOTH) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });
    });
    
    // Default excludes, checkbox
    h::div(array('class' => 'label'), 'Default excludes?');
    h::div(array('class' => 'checkboxContainer'), function() use ($o) {
      $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'defaultExcludes',);
      if ($o->getDefaultExcludes()) {
        $params['checked'] = 'checked';
      }
      h::input($params);
    });
    // Dir, textfield
    h::div(array('class' => 'label'), 'Base dir <span class="fineprintLabel">(relative to project sources. Empty is at root)</span>');
    h::div(array('class' => 'textfieldContainer'), function() use ($o) {
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => (substr($o->getDir(), strlen($GLOBALS['project']->getScmLocalWorkingCopy())))));
    });
    $includesLine = '';
    if ($o->getInclude()) {
      $includes = $o->getInclude();
      foreach ($includes as $include) {
        $includesLine .= $include . ', ';
      }
      // TODO: Oh god... Seriously do this better:
      if (!empty($includesLine)) {
        $includesLine = substr($includesLine, 0, strlen($includesLine)-2); // Oh god 2x...
      }
    }
    // Includes, textfield
    h::div(array('class' => 'label'), 'Files/dirs to include');
    h::div(array('class' => 'textfieldContainer'), function() use ($o, $includesLine) {
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'include', 'value' => $includesLine));
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
    h::div(array('class' => 'label'), 'Files/dirs to exclude');
    h::div(array('class' => 'textfieldContainer'), function() use ($o, $excludesLine) {
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'exclude', 'value' => $excludesLine));
    });
  }
  
  static public function BuilderElement_Type_Property(BuilderElement_Type_Property $o)
  {
    h::li(array('class' => 'builderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::builderElementTitle(array('title' => 'Property'));
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
}