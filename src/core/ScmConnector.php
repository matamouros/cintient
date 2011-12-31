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
 * A proxy class for use when dealing with SCM. Everything is managed
 * through here, no need to call (or even know) the concrete SCM
 * classes.
 *
 * @package     Scm
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class ScmConnector implements ScmConnectorInterface
{
  private $_connector;

  /**
   * Constructor
   *
   * @param string $type git|svn as possible values
   * @return boolean
   */
  public function __construct($type, $local, $remote, $username, $password)
  {
    $scmConnectorType = 'ScmConnector_' . ucfirst($type);
    if (!class_exists($scmConnectorType)) {
      SystemEvent::raise(SystemEvent::ERROR, "Unknown connector specified [CONNECTOR={$scmConnectorType}]", __METHOD__);
      return false;
    }
    $this->_connector = new $scmConnectorType($local, $remote, $username, $password);
  }

  /**
   * Deletes a local dir used to manage a remote SCM repository. This is
   * tipically used for resetting the sources of a project (good for
   * when the user changes SCM settings).
   *
   * @param string $local
   */
  static public function delete($local)
  {
    $ret = false;
    if (!empty($local)) {
      $ret = Framework_Filesystem::removeDir($local);
    }
    #if DEBUG
    if ($ret) {
      SystemEvent::raise(SystemEvent::DEBUG, "Deleted local working copy. [DIR={$local}]", __METHOD__);
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Could not delete local working copy. [DIR={$local}]", __METHOD__);
    }
    #endif
    return $ret;
  }


  public function checkout()
  {
    $local = $this->_connector->getLocal();
    $remote = $this->_connector->getRemote();
    if (empty($remote) || empty($local)) {
      return false;
    }
    SystemEvent::raise(SystemEvent::DEBUG, "Trying to check out remote repository... [REPOSITORY={$remote}]", __METHOD__);
    if (!file_exists($local)) {
      if (!mkdir($local, DEFAULT_DIR_MASK, true)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not create local working copy dir. [DIR={$local}]", __METHOD__);
        return false;
      }
    }
    if (!$this->_connector->checkout()) {
      SystemEvent::raise(SystemEvent::DEBUG, "Could not check out remote repository. [REPOSITORY={$remote}]", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Checked out remote repository. [REPOSITORY={$remote}]", __METHOD__);
      return true;
    }
  }

  public function export($toDir)
  {
    return $this->_connector->export($toDir);
  }

  public function isModified()
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Checking remote repository for modifications... [REPOSITORY={$this->_connector->getRemote()}]", __METHOD__);
    if (!$this->_connector->isModified()) {
      SystemEvent::raise(SystemEvent::DEBUG, "No modifications found. [REPOSITORY={$this->_connector->getRemote()}]", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Modifications were found! [REPOSITORY={$this->_connector->getRemote()}]", __METHOD__);
      return true;
    }
  }

  public function update(&$rev)
  {
    if (!$this->_connector->update($rev)) {
      SystemEvent::raise(SystemEvent::INFO, "Could not update local working copy, trying a few tricks before quiting. [DIR={$this->_connector->getLocal()}]", __METHOD__);
      if (!is_writable($this->_connector->getLocal()) && !@mkdir($this->_connector->getLocal(), DEFAULT_DIR_MASK, true))  {
        SystemEvent::raise(SystemEvent::INFO, "Could not update local working copy, dir was either not writable or didn't exist. [DIR={$this->_connector->getLocal()}]", __METHOD__);
        return false;
      }
      if (!$this->checkout()) {
        SystemEvent::raise(SystemEvent::INFO, "Could not update local working copy, trying 'checking out' the sources again, but couldn't. [DIR={$this->_connector->getLocal()}]", __METHOD__);
        return false;
      }
      if (!$this->_connector->update($rev)) {
        SystemEvent::raise(SystemEvent::INFO, "Definitely could not update local working copy. [DIR={$this->_connector->getLocal()}]", __METHOD__);
        return false;
      }
    }
    SystemEvent::raise(SystemEvent::DEBUG, "Updated local working copy. [DIR={$this->_connector->getLocal()}]", __METHOD__);
    return true;
  }

  public function tag() {}

  /**
   * Gets available connectors installed in the ScmConnector dir.
   *
   * @return array An alphabetically sorted array with the available connectors.
   */
  static public function &getAvailableConnectors()
  {
    $connectors = array();
    if ($dirHandle = opendir(CINTIENT_INSTALL_DIR . 'src/core/ScmConnector/')) {
      while (false !== ($filename = readdir($dirHandle))) {
        if (($connector = strstr($filename, '.php', true)) !== false) {
          $connectors[] = strtolower($connector);
        }
      }
    }
    sort($connectors);
    return $connectors;
  }
}