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
 * Database class.
 */
class Database
{
  static private $_transacting;
  
  /**
   * Instantiates a single class instance
   */
  private static function _singleton()
  {
    static $instance;
    if (!($instance instanceof SQLite3)) {
      $instance = new SQLite3(CINTIENT_DATABASE_FILE);
      #if DEBUG
      SystemEvent::raise(SystemEvent::DEBUG, 'New connection opened.', __METHOD__);
      #endif
      if (!($instance instanceof SQLite3)) {
        SystemEvent::raise(SystemEvent::ERROR, "Error connecting to the database.", __METHOD__);
        $instance = null;
        unset($instance);
        return false;
      }
    }
    return $instance;
  }

  /**
   * 
   * @param string $query The SQL to execute
   * @param array $values Optional values array for parameter binding
   * 
   * @return bool True or False.
   */
  static public function execute($query, $values=null)
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    $starttime = microtime(true);
    if (empty($values)) {
      if ($db->exec($query) === false) {
        SystemEvent::raise(SystemEvent::ERROR, 'Error executing query. [ERRNO='.$db->lastErrorCode().'] [ERRMSG='.$db->lastErrorMsg().'] [QUERY='.$query.']'.(!empty($values)?' [VALUES='.$tmp.']':''), __METHOD__);
        return false;
      }
    } else {
      if (!$stmt = self::_prepareAndBindValues($query, $values)) {
        SystemEvent::raise(SystemEvent::ERROR, 'Error binding parameters. [ERRNO='.$db->lastErrorCode().'] [ERRMSG='.$db->lastErrorMsg().'] [QUERY='.$query.']'.(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        return false;
      }
      if ($stmt->execute() === false) {
        SystemEvent::raise(SystemEvent::ERROR, 'Error executing statement. [ERRNO='.$db->lastErrorCode().'] [ERRMSG='.$db->lastErrorMsg().'] [QUERY='.$query.']'.(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        return false;
      }
    }
    $proctime = microtime(true)-$starttime;
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, 'Executed. [TIME='.sprintf('%.5f',$proctime).'] [SQL='.$query.']'.(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
    #endif
    return true;
  }
  
  /**
   * Specifically tailored for insertions, thus not needing to return a result
   * set. It is possible to use binding parameters to the sql query passed as an
   * argument. It behaves much like if it was doing a prepared statement (and
   * indeed it is) - however, performance-wise there's really no gain, since on
   * every call to this method, a Prepare() is done. I.e., use this "prepared
   * statement" functionality only for parameter binding (you gain automatic
   * protection against SQL injections), and use the statementPrepare() and
   * statementExecute() methods for the real prepared statements you may want to
   * use.
   * 
   * @param string $query The SQL to execute
   * @param array $values Optional values array for parameter binding
   * 
   * @return The id of the inserted record or False. NOTE: If the table doesn't
   * have auto-numbering on, the id string "0" is returned! Be sure to check
   * this using the === operator.
   */
  static public function insert($query, $values=null)
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    if (self::query($query, $values) !== false) {
      return $db->lastInsertRowID();
    }
    return false;
  }

  /**
   * Method specifically designed for queries that return a result set. It is
   * possible to use binding parameters to the sql query passed as an argument.
   * It behaves much like if it was doing a prepared statement (and indeed it
   * is) - however, performance-wise there's really no gain, since on every call
   * to this method, a Prepare() is done. I.e., use this "prepared statement"
   * functionality only for parameter binding (you gain automatic protection
   * against SQL injections), and use the statementPrepare() and
   * statementExecute() methods for the real prepared statements you may want to
   * use.
   * 
   * @param string $query The SQL to execute
   * @param array $values Optional values array for parameter binding
   * 
   * @return bool|Object False or the result set of the query performed
   */
  static public function query($query, $values = null)
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    $starttime = microtime(true);
    $rs = false;
    if (empty($values)) {
      $ret = $db->query($query);
    } else {
      if (!empty($values)) {
        $tmp = implode(' | ',$values);
      }
      if (!$stmt = self::_prepareAndBindValues($query, $values)) {
        SystemEvent::raise(SystemEvent::ERROR, 'Error binding parameters. [ERRNO='.$db->lastErrorCode().'] [ERRMSG='.$db->lastErrorMsg().'] [QUERY='.$query.']'.(!empty($values)?' [VALUES='.$tmp.']':''), __METHOD__);
        return false;
      }
      $ret = $stmt->execute();
    }
    $proctime = microtime(true)-$starttime;
    $tmp = '';
    if (!$rs = new Resultset($ret)) {
      SystemEvent::raise(SystemEvent::ERROR, 'Error executing. [ERRNO='.$db->lastErrorCode().'] [ERRMSG='.$db->lastErrorMsg().'] [QUERY='.$query.']'.(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, 'Executed. [TIME='.sprintf('%.5f',$proctime).'] [SQL='.$query.']'.(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
    #endif
    return $rs;
  }
  
  static public function stmtPrepare($query)
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    SystemEvent::raise(SystemEvent::DEBUG, 'Preparing.', __METHOD__);
    $paramIndex = 0;
    //
    // Substitute ? for :1 type placeholders, since SQLite3 doesn't use the
    // MySQL ? type placeholders and we don't want that to propagate out of
    // this database handler class.
    //
    $query = preg_replace_callback(
      '/\?/',
      function ($match) use (&$paramIndex) {
        $match = ':' . $paramIndex;
        $paramIndex++;
        return $match;
      },
      $query,
      -1
    );
    if ($query === null) {
      //TODO: error and abort
      return false;
    }
    $stmt = $db->prepare($query);
    return $stmt;
  }
  
  static public function stmtBind(SQLite3Stmt &$stmt, array $values)
  {
    SystemEvent::raise(SystemEvent::DEBUG, 'Binding.', __METHOD__);
    //
    // Fuck support for SQLite3 BLOB
    //
    for ($i = 0; $i < count($values); $i++) {
      $type = SQLITE3_TEXT;
      if (is_null($values[$i])) {
        $type = SQLITE3_NULL;
      } elseif (is_int($values[$i])) {
        $type = SQLITE3_INTEGER;
      } elseif (is_float($values[$i])) {
        $type = SQLITE3_FLOAT;
      }
      if (!$stmt->bindValue(':' . $i, $values[$i], $type)) {
        return false;
      }
    }
  }
  
  static public function stmtExecute(SQLite3Stmt &$stmt)
  {
    SystemEvent::raise(SystemEvent::DEBUG, 'Executing.', __METHOD__);
    return $stmt->execute();
  }
  
  static public function beginTransaction()
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    if (self::$_transacting) {
      SystemEvent::raise(SystemEvent::ERROR, "Can't begin transaction, there's one pending.", __METHOD__);
      return false;
    }
    $sql = "BEGIN TRANSACTION";
    if (!$db->exec($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not begin transaction.", __METHOD__);
      return false;
    } else {
      self::$_transacting = true;
      SystemEvent::raise(SystemEvent::DEBUG, "Transaction started.", __METHOD__);
      return true;
    }
  }
  
  static public function endTransaction()
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    if (!self::$_transacting) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't end transaction, there wasn't one pending.", __METHOD__);
      return false;
    }
    $sql = "END TRANSACTION";
    if (!$db->exec($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not commit transaction.", __METHOD__);
      return false;
    } else {
      self::$_transacting = false;
      SystemEvent::raise(SystemEvent::DEBUG, "Transaction commited.", __METHOD__);
      return true;
    }
  }
  
  static public function rollbackTransaction()
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    if (!self::$_transacting) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't rollback transaction, there wasn't one pending.", __METHOD__);
      return false;
    }
    $sql = "ROLLBACK TRANSACTION";
    if (!$db->exec($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Couldn't rollback transaction.", __METHOD__);
      return false;
    } else {
      self::$_transacting = false;
      SystemEvent::raise(SystemEvent::ERROR, "Transaction rolledback.", __METHOD__);
      return true;
    }
  }
  
  static private function _prepareAndBindValues(&$query, &$values)
  {
    $db = self::_singleton();
    if (!$db) {
      return false;
    }
    $paramIndex = 0;
    $numReplaces = 0;
    //
    // Substitute ? for :1 type placeholders, since SQLite3 doesn't use the
    // MySQL ? type placeholders and we don't want that to propagate out of
    // this database handler class.
    //
    $query = preg_replace_callback(
      '/(%)?\?(%)?/',
      function ($matches) use (&$paramIndex, &$values) {
        $match = ':' . $paramIndex;
        if (isset($matches[1])) {
          $values[$paramIndex] = $matches[1] . $values[$paramIndex];
        }
        if (isset($matches[2])) {
          $values[$paramIndex] .= $matches[2];
        }
        $paramIndex++;
        return $match;
      },
      $query,
      -1,
      $numReplaces
    );
    if ($query === null) {
      //TODO: error and abort
      return false;
    }
    $stmt = $db->prepare($query);
    if (!($stmt instanceof SQLite3Stmt)) {
      return false;
    }
    //
    // Fuck support for SQLite3 BLOB
    //
    for ($i = 0; $i < $numReplaces; $i++) {
      $type = SQLITE3_TEXT;
      if (is_null($values[$i])) {
        $type = SQLITE3_NULL;
      } elseif (is_int($values[$i])) {
        $type = SQLITE3_INTEGER;
      } elseif (is_float($values[$i])) {
        $type = SQLITE3_FLOAT;
      }
      if (!$stmt->bindValue(':' . $i, $values[$i], $type)) {
        return false;
      }
    }
    return $stmt;
  }
}
