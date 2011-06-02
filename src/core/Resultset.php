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
 * @package Database
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