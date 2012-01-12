<?php
/*
 *
 *  Cintient, Continuous Integration made simple.
 *  Copyright (c) 2010-2012, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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
 * Because of the way we use __call and $this->_changed, this class shouldn't
 * have null values for class variables populated from the database.
 *
 * One special case with this class is that all data persistence handling is
 * done automatically, i.e., there's no need to call save() from an outside
 * scope. Usage is simple: new objects created from scratch, don't forget to
 * call init(); objects created from the database, no need to do anything.
 *
 * @package     Framework
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class SystemSettings extends Framework_DatabaseObjectAbstract implements ArrayAccess
{
  protected $_settings;

  const ALLOW_USER_REGISTRATION = 'allowUserRegistration'; // bool
  const EXECUTABLE_GIT          = 'executableGit';
  const EXECUTABLE_PHP          = 'executablePhp';
  const EXECUTABLE_SVN          = 'executableSvn';
  const EXECUTABLE_TAR          = 'executableTar';
  const INTERNAL_BUILDER_ACTIVE = 'internalBuilderActive'; // bool
  const VERSION                 = 'version';

  static public $packageGenerationCmd = array(
  	self::EXECUTABLE_TAR => 'cd ${tmpDir} && ${archiverExecutable} -czf ${releaseLabel}.tar.gz ${sourcesDir}',
  );

  public function __construct()
  {
    parent::__construct();

    //
    // Make sure that bool valued settings are cast to ints.
    //
    $this->_settings = array(
      self::ALLOW_USER_REGISTRATION => 1,
      self::EXECUTABLE_TAR => (Framework_HostOs::isWindows() ? '' : 'tar'),
      self::EXECUTABLE_GIT => 'git' . (Framework_HostOs::isWindows() ? '.exe' : ''),
      self::EXECUTABLE_PHP => 'php' . (Framework_HostOs::isWindows() ? '.exe' : '')
                                    . (php_ini_loaded_file() ? ' -c '. htmlentities(str_replace(array('\\', '//'), '/', php_ini_loaded_file())) : ''), // htmlentities because of lib.htmlgen.php
      self::EXECUTABLE_SVN => 'svn' . (Framework_HostOs::isWindows() ? '.exe' : ''),
      self::INTERNAL_BUILDER_ACTIVE => CINTIENT_INTERNAL_BUILDER_ACTIVE,
      self::VERSION => '',
    );
  }

  public function getCmdForPackageGeneration(array $params = array())
  {
    $command = self::$packageGenerationCmd[$params['archiverExecutable']];
    preg_match_all('/\$\{(\w+)\}/', $command, $matches);
    $search = array();
    $replace = array();
    foreach ($matches[1] as $property) {
      if (!array_key_exists($property, $params)) {
        SystemEvent::raise(SystemEvent::ERROR, "Missing required parameter for package generation command.", __METHOD__);
        $params[$property] = '';
      }
      $search[] = '${' . $property . '}';
      if ($property == 'archiverExecutable') {
        $replace[] = $this[$params['archiverExecutable']]; // fetch the executable of the provided archiver
      } else {
        $replace[] = $params[$property];
      }
    }
    return str_replace($search, $replace, $command);
  }

  public function getViewGlobalSettings()
  {
    require_once 'lib/lib.htmlgen.php';
    $o = $this;

    h::div(array('class' => 'clearfix'), function () use ($o) {
      h::label(array('for' => SystemSettings::ALLOW_USER_REGISTRATION), 'Allow registration?');
      h::div(array('class' => 'input'), function () use ($o) {
        h::ul(array('class' => 'inputs-list'), function () use ($o) {
          h::li(function () use ($o) {
            h::label(function () use ($o) {
              $inputParams = array('type' => 'checkbox', 'name' => SystemSettings::ALLOW_USER_REGISTRATION);
              if ($o[SystemSettings::ALLOW_USER_REGISTRATION]) {
                $inputParams['checked'] = 'checked';
              }
              h::input($inputParams);
              h::span(array('class' => 'help-block'), "Allows user registration on the authentication prompt.");
            });
          });
        });
      });
    });

    h::div(array('class' => 'clearfix'), function () use ($o) {
      h::label(array('for' => SystemSettings::INTERNAL_BUILDER_ACTIVE), 'Internal builder?');
      h::div(array('class' => 'input'), function () use ($o) {
        h::ul(array('class' => 'inputs-list'), function () use ($o) {
          h::li(function () use ($o) {
            h::label(function () use ($o) {
              $inputParams = array('type' => 'checkbox', 'name' => SystemSettings::INTERNAL_BUILDER_ACTIVE);
              if ($o[SystemSettings::INTERNAL_BUILDER_ACTIVE]) {
                $inputParams['checked'] = 'checked';
              }
              h::input($inputParams);
              h::span(array('class' => 'help-block'), "Activates the internal automatic integration builder. Use this if you can't setup supervise or similar on the host system.");
            });
          });
        });
      });
    });
  }

  public function getViewExecutables()
  {
    require_once 'lib/lib.htmlgen.php';
    $o = $this;

    h::fieldset(function () use ($o) {
      h::div(array('class' => 'clearfix'), function () use ($o) {
        h::label(array('for' => SystemSettings::EXECUTABLE_PHP), 'PHP');
        h::div(array('class' => 'input'), function () use ($o) {
          h::input(array('type' => 'text', 'class' => 'span6', 'name' => SystemSettings::EXECUTABLE_PHP, 'value' => $o[SystemSettings::EXECUTABLE_PHP]));
          h::span(array('class' => 'help-block'), "The path to the host system's PHP executable.");
        });
      });

      h::div(array('class' => 'clearfix'), function () use ($o) {
        h::label(array('for' => SystemSettings::EXECUTABLE_GIT), 'Git');
        h::div(array('class' => 'input'), function () use ($o) {
          h::input(array('type' => 'text', 'class' => 'span6', 'name' => SystemSettings::EXECUTABLE_GIT, 'value' => $o[SystemSettings::EXECUTABLE_GIT]));
          h::span(array('class' => 'help-block'), "The path to the host system's Git executable. Required in order to allow Git as the configured SCM for projects.");
        });
      });

      h::div(array('class' => 'clearfix'), function () use ($o) {
        h::label(array('for' => SystemSettings::EXECUTABLE_SVN), 'SVN');
        h::div(array('class' => 'input'), function () use ($o) {
          h::input(array('type' => 'text', 'class' => 'span6', 'name' => SystemSettings::EXECUTABLE_SVN, 'value' => $o[SystemSettings::EXECUTABLE_SVN]));
          h::span(array('class' => 'help-block'), "The path to the host system's SVN executable. Required in order to allow SVN as the configured SCM for projects.");
        });
      });
/*    });

    h::fieldset(function () use ($o) {
      h::legend('Archivers');*/
      h::div(array('class' => 'clearfix'), function () use ($o) {
        h::label(array('for' => SystemSettings::EXECUTABLE_TAR), 'tar');
        h::div(array('class' => 'input'), function () use ($o) {
          h::input(array('type' => 'text', 'class' => 'span6', 'name' => SystemSettings::EXECUTABLE_TAR, 'value' => $o[SystemSettings::EXECUTABLE_TAR]));
          h::span(array('class' => 'help-block'), "The path to the host system's tar executable. Will be used to generate all release packages.");
        });
      });
    });
  }

  public function init() {}

  /**
   * Overloading for @see ArrayAccess::offsetSet()
   */
  public function offsetSet($offset, $value)
  {
    if (is_null($offset)) {
      $this->_settings[] = $value;
    } else {
      $this->_settings[$offset] = $value;
    }
  }

  /**
   * Overloading for @see ArrayAccess::offsetExists()
   */
  public function offsetExists($offset)
  {
    return isset($this->_settings[$offset]);
  }

  /**
   * Overloading for @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($offset)
  {
    unset($this->_settings[$offset]);
  }

  /**
   * Overload for @see ArrayAccess::offsetGet()
   */
  public function offsetGet($offset)
  {
    return isset($this->_settings[$offset]) ? $this->_settings[$offset] : null;
  }

  protected function _save($force = false)
  {
    if (!$this->_autoSave) {
      return true;
    }
    if (!$this->hasChanged()) {
      if (!$force) {
        return false;
      }
      SystemEvent::raise(SystemEvent::DEBUG, "Forced object save.", __METHOD__);
    }

    if (!$stmt = Database::stmtPrepare("REPLACE INTO systemsettings (key, value) VALUES (?,?)")) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems trying to save system settings.", __METHOD__);
      return false;
    }

    foreach ($this->_settings as $key => $value) {
      Database::stmtBind($stmt, array($key, $value));
      if (!Database::stmtExecute($stmt)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems saving system settings.", __METHOD__);
        return false;
      }
    }

    SystemEvent::raise(SystemEvent::DEBUG, "Saved system settings.", __METHOD__);

    $this->resetSignature();
    return true;
  }

  static public function load()
  {
    $ret = false;
    $sql = 'SELECT * FROM systemsettings';
    if ($rs = Database::query($sql)) {
      $ret = self::_getObject($rs);
    }
    return $ret;
  }

  /**
   *
   * @param Resultset $rs
   */
  static private function _getObject(Resultset $rs)
  {
    $ret = new self();
    while ($rs->NextRow()) {
      $ret->setSetting($rs->getKey(),$rs->getValue());
    }
    $ret->resetSignature();
    return $ret;
  }

  static public function install()
  {
    SystemEvent::raise(SystemEvent::INFO, "Creating systemsettings table...", __METHOD__);

    $tableName = 'systemsettings';
    $sql = <<<EOT
DROP TABLE IF EXISTS {$tableName}NEW;
CREATE TABLE IF NOT EXISTS {$tableName}NEW(
  key VARCHAR(255) PRIMARY KEY,
  value TEXT NOT NULL DEFAULT ''
);
EOT;
    if (!Database::setupTable($tableName, $sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems setting up $tableName table.", __METHOD__);
      return false;
    }

    $self = new SystemSettings();
    $self->_save(true); // This allows us to save the default system settings values at install time.

    SystemEvent::raise(SystemEvent::INFO, "$tableName table created.", __METHOD__);
    return true;
  }

  public function setSetting($key, $value)
  {
    // Validate if new setting is valid (must have been set in the constructor)
    if (isset($this->_settings[$key])) {
      $this->_settings[$key] = $value;
    }
  }
}