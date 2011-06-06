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
 * @package Builder
 */
class BuilderConnector_Html
{
  /**
   *
   * Helper function for centralizing all Build Element's title HTML
   * @param Array $params
   */
  static public function Build_BuilderElementTitle(Build_BuilderElement $o, Array $params = array())
  {
    h::div(array('class' => 'Build_BuilderElementTitle'), function() use ($o, $params) {
      h::p(array('class' => 'title'), $params['title']);
      h::ul(array('class' => 'options'), function() use ($o) {
        if ($o->isEditable()) {
          h::li(function() {h::a('save', '#', array('class' => 'submit'));});
        }
        if ($o->isEditable() && $o->isDeletable()) {
          h::li(function() {h::p(array('class' => 'pipe'), ' | ');});
        }
        if ($o->isDeletable()) {
          h::li(function() {h::a('x', '#', array('class' => 'delete'));});
        }
      });
    });
  }

  static public function Build_BuilderElement_Project(Build_BuilderElement_Project $o)
  {
    h::set_indent_pattern('  ');
    /*
    h::div(array('class' => 'Build_BuilderElement'), function() use ($o) {
      h::div(array('class' => 'Build_BuilderElementTitle'), 'Project');
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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
          BuilderConnector_Html::Build_BuilderElement_Target($target);
        }
      }
      //h::input(array('type' => 'submit', 'value' => 'Save all!', 'id' => 'submitButton'));
    //});
  }

  static public function Build_BuilderElement_Target(Build_BuilderElement_Target $o)
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

  static public function Build_BuilderElement_Task_Filesystem_Chmod(Build_BuilderElement_Task_Filesystem_Chmod $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Chmod'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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
            BuilderConnector_Html::Build_BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }

  static public function Build_BuilderElement_Task_Filesystem_Chown(Build_BuilderElement_Task_Filesystem_Chown $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Chown'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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
            BuilderConnector_Html::Build_BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }

  static public function Build_BuilderElement_Task_Filesystem_Copy(Build_BuilderElement_Task_Filesystem_Copy $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Copy'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
        // Fail on error, checkbox
        h::div(array('class' => 'label'), 'Fail on error?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnError',);
          if ($o->getFailOnError()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // File, textfield
        h::div(array('class' => 'label'), 'File');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'file', 'value' => $o->getFile()));
        });
        // To file, textfield
        h::div(array('class' => 'label'), 'Destination file');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'toFile', 'value' => $o->getToFile()));
        });
        // To dir, textfield
        h::div(array('class' => 'label'), 'Destination dir');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'toDir', 'value' => $o->getToDir()));
        });
        // Filesets
        if ($o->getFilesets()) {
          $filesets = $o->getFilesets();
          foreach ($filesets as $fileset) {
            // self:: doesn't work here...
            BuilderConnector_Html::Build_BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }

  static public function Build_BuilderElement_Task_Filesystem_Delete(Build_BuilderElement_Task_Filesystem_Delete $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Delete'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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
            BuilderConnector_Html::Build_BuilderElement_Type_Fileset($fileset);
          }
        }
      });
    });
  }

  static public function Build_BuilderElement_Task_Echo(Build_BuilderElement_Task_Echo $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Echo'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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

  static public function Build_BuilderElement_Task_Exec(Build_BuilderElement_Task_Exec $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Exec'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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

  static public function Build_BuilderElement_Task_Filesystem_Mkdir(Build_BuilderElement_Task_Filesystem_Mkdir $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Mkdir'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
        // Dir, textfield
        h::div(array('class' => 'label'), 'Dir');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => $o->getDir()));
        });
      });
    });
  }

  static public function Build_BuilderElement_Task_PhpDepend(Build_BuilderElement_Task_PhpDepend $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'PhpDepend'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
        // Fail on error, checkbox
        h::div(array('class' => 'label'), 'Fail on error?');
        h::div(array('class' => 'checkboxContainer'), function() use ($o) {
          $params = array('class' => 'checkbox', 'type' => 'checkbox', 'name' => 'failOnError',);
          if ($o->getFailOnError()) {
            $params['checked'] = 'checked';
          }
          h::input($params);
        });
        // Include Dirs, textfield
        h::div(array('class' => 'label'), 'Include dirs <span class="fineprintLabel">(space separated)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'includeDirs', 'value' => $o->getIncludeDirs()));
        });
        // Exclude Dirs, textfield
        h::div(array('class' => 'label'), 'Exclude dirs <span class="fineprintLabel">(space separated)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'excludeDirs', 'value' => $o->getExcludeDirs()));
        });
        // Exclude packages, textfield
        h::div(array('class' => 'label'), 'Exclude packages <span class="fineprintLabel">(space separated)</span>');
        h::div(array('class' => 'textfieldContainer'), function() use ($o) {
          h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'excludePackages', 'value' => $o->getExcludePackages()));
        });
      });
    });
  }

  static public function Build_BuilderElement_Task_PhpLint(Build_BuilderElement_Task_PhpLint $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'PhpLint'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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
            BuilderConnector_Html::Build_BuilderElement_Type_Fileset($fileset);
          }
        }
        // TODO: Add HTML button for adding new fileset.
      });
    });
  }

  static public function Build_BuilderElement_Task_PhpUnit(Build_BuilderElement_Task_PhpUnit $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'PhpUnit'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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
            BuilderConnector_Html::Build_BuilderElement_Type_Fileset($fileset);
          }
        }
        // TODO: Add HTML button for adding new fileset.
      });
    });
  }

  static public function Build_BuilderElement_Type_Fileset(Build_BuilderElement_Type_Fileset $o, Build_BuilderElement $parent = null)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::hr();
    if (empty($parent)) {
    }
    // Type, radio button
    h::ul(array('class' => 'radioContainer'), function() use ($o) {
      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Files only');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => Build_BuilderElement_Type_Fileset::FILE);
        if ($o->getType() == Build_BuilderElement_Type_Fileset::FILE) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });
      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Dirs only');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => Build_BuilderElement_Type_Fileset::DIR);
        if ($o->getType() == Build_BuilderElement_Type_Fileset::DIR) {
          $params['checked'] = 'checked';
        }
        h::input($params);
      });

      h::li(function() use ($o) {
        h::div(array('class' => 'label'), 'Both');
        $params = array('class' => 'radio', 'type' => 'radio', 'name' => 'type', 'value' => Build_BuilderElement_Type_Fileset::BOTH);
        if ($o->getType() == Build_BuilderElement_Type_Fileset::BOTH) {
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
    h::div(array('class' => 'label'), 'Base dir');
    h::div(array('class' => 'textfieldContainer'), function() use ($o) {
      //h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => (substr($o->getDir(), strlen($GLOBALS['project']->getScmLocalWorkingCopy())))));
      h::input(array('class' => 'textfield', 'type' => 'text', 'name' => 'dir', 'value' => $o->getDir()));
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

  static public function Build_BuilderElement_Type_Properties(Build_BuilderElement_Type_Properties $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Properties'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
        // Name, textfield
        h::div(array('class' => 'label'), 'Key=value pairs <span class="fineprintLabel">(Lines started with ; are comments)</span>');
        h::div(array('class' => 'textareaContainer'), function() use ($o) {
          h::textarea(array('name' => 'text'), $o->getText());
        });
      });
    });
  }

  static public function Build_BuilderElement_Type_Property(Build_BuilderElement_Type_Property $o)
  {
    if (!$o->isVisible()) {
      return true;
    }
    h::li(array('class' => 'Build_BuilderElement', 'id' => $o->getInternalId()), function() use ($o) {
      BuilderConnector_Html::Build_BuilderElementTitle($o, array('title' => 'Property'));
      h::div(array('class' => 'Build_BuilderElementForm'), function() use ($o) {
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