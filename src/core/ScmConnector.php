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
 * @package Scm
 */
class ScmConnector
{
  static public function delete(array $params = array())
  {
    if (isset($params['local']) && !empty($params['local'])) {
      $ret = unlink($params['local']);
    }
    #if DEBUG
    if ($ret) {
      SystemEvent::raise(SystemEvent::DEBUG, "Deleted local working copy. [DIR={$params['local']}]", __METHOD__);
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Could not delete local working copy. [DIR={$params['local']}]", __METHOD__);
    }
    #endif
    return $ret;
  }

  static public function checkout(array $params = array())
  {
    isset($params['type'])?:$params['type']=SCM_DEFAULT_CONNECTOR;
    if (!isset($params['remote']) || !isset($params['local']) ||
         empty($params['remote']) ||  empty($params['local']) )
    {
      return false;
    }
    SystemEvent::raise(SystemEvent::DEBUG, "Trying to check out remote repository... [REPOSITORY={$params['remote']}]", __METHOD__);
    if (!file_exists($params['local'])) {
      if (!mkdir($params['local'], DEFAULT_DIR_MASK, true)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not create local working copy dir. [DIR={$params['local']}]", __METHOD__);
        return false;
      }
    }
    $scmConnectorObject = 'ScmConnector_' . ucfirst($params['type']);
    if (!$scmConnectorObject::checkout($params)) {
      SystemEvent::raise(SystemEvent::DEBUG, "Could not check out remote repository. [REPOSITORY={$params['remote']}]", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Checked out remote repository. [REPOSITORY={$params['remote']}]", __METHOD__);
      return true;
    }
  }

  static public function isModified(array $params = array())
  {
    isset($params['type'])?:$params['type']=SCM_DEFAULT_CONNECTOR;
    $scmConnectorObject = 'ScmConnector_' . ucfirst($params['type']);
    SystemEvent::raise(SystemEvent::DEBUG, "Checking remote repository for modifications... [REPOSITORY={$params['remote']}]", __METHOD__);
    if (!$scmConnectorObject::isModified($params)) {
      SystemEvent::raise(SystemEvent::DEBUG, "No modifications found. [REPOSITORY={$params['remote']}]", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Modifications were found! [REPOSITORY={$params['remote']}]", __METHOD__);
      return true;
    }
  }

  static public function update(array $params = array(), &$rev)
  {
    isset($params['type'])?:$params['type']=SCM_DEFAULT_CONNECTOR;
    $scmConnectorObject = 'ScmConnector_' . ucfirst($params['type']);
    if (!$scmConnectorObject::update($params, $rev)) {
      SystemEvent::raise(SystemEvent::DEBUG, "Could not update local working copy. [DIR={$params['local']}]", __METHOD__);
      return false;
    } else {
      SystemEvent::raise(SystemEvent::DEBUG, "Updated local working copy. [DIR={$params['local']}]", __METHOD__);
      return true;
    }
  }

  static public function tag() {}

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