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
 * Because of the way we use __call and $this->_changed, this class shouldn't
 * have null values for class variables populated from the database.
 * 
 * One special case with this class is that all data persistence handling is
 * done automatically, i.e., there's no need to call save() from an outside
 * scope. Usage is simple: new objects created from scratch, don't forget to
 * call init(); objects created from the database, no need to do anything.
 */
class SystemSettings
{
  private $_dateInstalled;

  /**
   * Magic method implementation for calling vanilla getters and setters. This
   * is rigged to work only with private/protected non-static class variables
   * whose nomenclature follows the Zend Coding Standard.
   * 
   * @param $name
   * @param $args
   */
  public function __call($name, $args)
  {
    if (strpos($name, 'get') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      return $this->$var;
    } elseif (strpos($name, 'set') === 0) {
      $var = '_' . lcfirst(substr($name, 3));
      if ($this->$var !== null) {
        $this->_changed = true;
      }
      $this->$var = $args[0];
      return true;
    }
    return false;
  }
  
  public function __construct()
  {
    
  }
  
  public function __destruct()
  {
    if ($this->_changed) {
      $this->_save();
    }
  }
  
  public function build()
  {
    
  }
  
  /**
   * Call this at the very creation of the project, for checking out the sources
   * and initialization stuff like that.
   */
  public function init()
  {
    /*
    if (!$this->_save()) {
      return false;
    }
    //
    // We can handle checkout failures, the user can retry it later. _save()
    // calls, on the other hand are not the same.
    //
    $this->_scmConnector->checkout();
    return true;
    */
    return $this->_scmConnector->checkout();
  }
  
  private function _save()
  {
    //TODO: database write logic
    $sql = 'INSERT INTO project'
         . ' (accesslevel,datebuild,datecheckedforchanges,datecreation,datemodification,description,title)'
         . " VALUES ()";
    $val = array();
    if (!($id = Database::insert($sql, $val)) || !is_numeric($id)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems saving to db.", __METHOD__);
      return false;
    }
    $this->setId($id);
    $this->setChanged(false);
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Saved. [PID={$this->getId()}] [TITLE={$this->getTitle()}]", __METHOD__);
    #endif
    return true;
  }
  
  static public function getById(User $user, $access, $id)
  {
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $id = (int)$id;
    $sql = 'SELECT p.*'
         . ' FROM project p, project_user pu'
         . ' WHERE p.id=?'
         . ' AND p.id=pu.id'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    $val = array($id, $user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      $ret = null;
      if (!$rs->EOF) {
        $ret = self::_getObject($rs);
      }
      @$rs->Close();
      $rs = null;
      unset($rs);
    }
    return $ret;
  }
  
  static public function &getList(User $user, $access, array $options = array())
  {
    isset($options['sortType']) ?: $options['sortType'] = Sort::ALPHA_ASC;
    
    $ret = false;
    $access = (int)$access; // Unfortunately, no enums, no type hinting, no cry.
    $sql = 'SELECT p.*'
         . ' FROM project p, project_user pu'
         . ' WHERE p.id=pu.id'
         . ' AND pu.userid=?'
         . ' AND pu.access & ?';
    if ($options['sortType'] != Sort::NONE) {
      $sql .= ' ORDER BY';
      switch ($options['sortType']) {
        case Sort::ALPHA_ASC:
          $sql .= ' title ASC';
          break;
        case Sort::ALPHA_DESC:
          $sql .= ' title DESC';
      }
    }
    $val = array($user->getId(), $access);
    if ($rs = Database::query($sql, $val)) {
      $ret = array();
      while (!$rs->EOF) {
        $ret[] = self::_getObject($rs);
        $rs->MoveNext();
      }
      @$rs->Close();
      $rs = null;
      unset($rs);
    }
    return $ret;
  }
  
  /**
   * 
   * @param unknown_type $rs
   */
  static private function _getObject($rs)
  {
    $ret = new self();
    $ret->setId($rs->fields['id']);
    $ret->setChanged(false);
    return $ret;
  }
}