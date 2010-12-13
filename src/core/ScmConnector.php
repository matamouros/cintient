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
class ScmConnector
{ 
  static public function delete(array $params = array())
  {
    if (isset($params['local']) && !empty($params['local'])) {
      return unlink($params['local']);
    }
    return false;
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
    $ret = $scmConnectorObject::checkout($params);
    if (!$ret) {
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
    return $scmConnectorObject::isModified($params);
  }
  
  static public function update(array $params = array())
  {
    isset($params['type'])?:$params['type']=SCM_DEFAULT_CONNECTOR;
    $scmConnectorObject = 'ScmConnector_' . ucfirst($type);
    return $scmConnectorObject::update($params);
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
    if ($dirHandle = opendir(INSTALL_DIR . 'src/core/ScmConnector/')) {
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