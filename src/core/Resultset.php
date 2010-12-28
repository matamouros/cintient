<?php
/*
 * Cintient, Continuous Integration made simple.
 * 
 * Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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
class Resultset
{
  private $_rs;
  private $_currentRow;
  
  public function __construct($rs)
  {
    $this->_rs = $rs;
    $this->_currentRow = false;
  }
  
  public function __destruct()
  {
    if (isset($this->_rs) && $this->_rs instanceof SQLite3Result) {
      $this->close();
    }
  }

  /**
   * 
   * @param $name
   * @param $args
   */
  public function __call($name, $args)
  {
    $var = strtolower(substr($name, 3));
    if (isset($this->_currentRow[$var])) {
      return $this->_currentRow[$var];
    }
    return false;
  }
  
  public function close()
  {
    $this->_rs->finalize();
    $this->_rs = null;
    unset($this->_rs);
    return true;
  }
  
  public function nextRow()
  {
    if (($this->_rs instanceof SQLite3Result) &&
       (($this->_currentRow = $this->_rs->fetchArray(SQLITE3_ASSOC)) !== false)
    ) {
      return true;
    } else {
      $this->_currentRow = false;
      return false;
    }
  }
}