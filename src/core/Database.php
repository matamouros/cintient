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
 * Database class, specifically tailored to use SQLite - if any other
 * engine is used we will need to add another layer of abstraction. This
 * was designed having in mind that SQLite locks at the database level
 * and that pretty much every method call can fail immediately due to
 * database locking. A mechanism for retrying on these circumstances was
 * implemented, and then the busyTimeout() call on the singleton call
 * completely solved all locking error symptoms. But even as it is, the
 * retry mechanisms will remain.
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
class Database
{
  static private $_transacting = 0;

  const DEFERRED_TRANSACTION = 1;
  const IMMEDIATE_TRANSACTION = 2;
  const EXCLUSIVE_TRANSACTION = 3;

  /**
   * Instantiates a single class instance
   */
  private static function _singleton()
  {
    static $instance;
    if (!($instance instanceof SQLite3)) {
      $instance = new SQLite3(CINTIENT_DATABASE_FILE);
      SystemEvent::raise(SystemEvent::DEBUG, 'New connection opened.', __METHOD__);
      if (!($instance instanceof SQLite3)) {
        SystemEvent::raise(SystemEvent::ERROR, "Error connecting to the database.", __METHOD__);
        $instance = null;
        unset($instance);
        //return false;
        die("Error connecting to the database.");
      }
      // Apparently doesn't work...
      $instance->busyTimeout(10000); // Set a busy timeout for 1.5 secs
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
    $result = false;
    $retries = 0;
    $starttime = microtime(true);
    do {
      if ($retries > 0) {
        SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        sleep(1);
      }
      if (empty($values)) {
        if (($result = $db->exec($query)) === false) {
          SystemEvent::raise(SystemEvent::ERROR, "Error executing query. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.$tmp.']':''), __METHOD__);
        }
      } else {
        // empty($stmt) is to make sure self::_prepareAndBindValues()
        // only runs the first time around
        if (empty($stmt) && !$stmt = self::_prepareAndBindValues($query, $values)) {
          SystemEvent::raise(SystemEvent::ERROR, "Error binding parameters. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        } else if (($result = @$stmt->execute()) === false) {
          SystemEvent::raise(SystemEvent::ERROR, "Error executing statement. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        }
      }
      $retries++;
      // SQLITE_BUSY || SQLITE_IOERR_BLOCKED (@see http://www.sqlite.org/c3ref/busy_timeout.html)
    } while (!$result && ($db->lastErrorCode() == 5 || $db->lastErrorCode() == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES);

    $proctime = microtime(true)-$starttime;
    SystemEvent::raise(SystemEvent::DEBUG, "Executed. [TIME=".sprintf('%.5f',$proctime)."] [SQL={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
    return (bool)$result;
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
    $result = false;
    $retries = 0;
    $starttime = microtime(true);
    do {
      if ($retries > 0) {
        SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        sleep(1);
      }
      if (empty($values)) {
        if (($result = $db->exec($query)) === false) {
          SystemEvent::raise(SystemEvent::ERROR, "Error inserting query. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.$tmp.']':''), __METHOD__);
        }
      } else {
        // empty($stmt) is to make sure self::_prepareAndBindValues()
        // only runs the first time around
        if (empty($stmt) && !$stmt = self::_prepareAndBindValues($query, $values)) {
          SystemEvent::raise(SystemEvent::ERROR, "Error binding parameters. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        } else if (($result = @$stmt->execute()) === false) {
          SystemEvent::raise(SystemEvent::ERROR, "Error executing statement. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        }
      }
      $retries++;
      // SQLITE_BUSY || SQLITE_IOERR_BLOCKED (@see http://www.sqlite.org/c3ref/busy_timeout.html)
    } while (!$result && ($db->lastErrorCode() == 5 || $db->lastErrorCode() == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES);

    $proctime = microtime(true)-$starttime;
    SystemEvent::raise(SystemEvent::DEBUG, "Inserted. [TIME=".sprintf('%.5f',$proctime)."] [SQL={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
    return ($result === false ? false : $db->lastInsertRowID());
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
    $result = false;
    $retries = 0;
    $starttime = microtime(true);
    do {
      if ($retries > 0) {
        SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        sleep(1);
      }
      if (empty($values)) {
        if (($result = $db->query($query)) === false) {
          SystemEvent::raise(SystemEvent::ERROR, "Error executing query. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        }
      } else {
        // empty($stmt) is to make sure self::_prepareAndBindValues()
        // only runs the first time around
        if (empty($stmt) && !$stmt = self::_prepareAndBindValues($query, $values)) {
          SystemEvent::raise(SystemEvent::ERROR, "Error binding parameters. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        } else if (($result = @$stmt->execute()) === false) {
          SystemEvent::raise(SystemEvent::ERROR, "Error executing statement. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        }
      }
      $retries++;
      // SQLITE_BUSY || SQLITE_IOERR_BLOCKED (@see http://www.sqlite.org/c3ref/busy_timeout.html)
    } while (!$result && ($db->lastErrorCode() == 5 || $db->lastErrorCode() == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES);

    $proctime = microtime(true)-$starttime;
    if (!$result || (!$rs = new Resultset($result))) {
      SystemEvent::raise(SystemEvent::ERROR, "Error querying. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
      return false;
    }
    SystemEvent::raise(SystemEvent::DEBUG, "Queried. [TIME=".sprintf('%.5f',$proctime)."] [SQL={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
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

  static public function beginTransaction($type = self::DEFERRED_TRANSACTION)
  {
    // Already part of a transaction, just increment the counter so that
    // only the transaction starter can end it.
    if (self::$_transacting > 0) {
      self::$_transacting++;
      return true;
    }
    $db = self::_singleton();
    $retries = 0;
    $errorCode = 5; // Just so we can enter the while loop.
    $typeText = '';
    if ($type == self::IMMEDIATE_TRANSACTION) {
      $typeText = 'IMMEDIATE ';
    } elseif ($type == self::EXCLUSIVE_TRANSACTION) {
      $typeText = 'EXCLUSIVE ';
    }
    $query = "BEGIN {$typeText}TRANSACTION";
    // SQLITE_BUSY || SQLITE_IOERR_BLOCKED (@see http://www.sqlite.org/c3ref/busy_timeout.html)
    while (self::$_transacting == 0 && ($errorCode == 5 || $errorCode == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES) {
      if ($retries > 0) {
        SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorCode()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        sleep(1);
      }
      if (!@$db->exec($query)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not begin transaction. [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}]", __METHOD__);
      } else {
        self::$_transacting++;
        SystemEvent::raise(SystemEvent::DEBUG, "Transaction started.", __METHOD__);
      }
      $retries++;
      $errorCode = $db->lastErrorCode();
    }
    return (self::$_transacting > 0);
  }

  /**
   * Requests to end a transaction must come from the initial
   * transaction starter, contrary to rollbackTransaction(), which
   * stops a transaction immediately and rolls it back.
   */
  static public function endTransaction()
  {
    // Already part of a transaction, just decrement the counter so that
    // only the transaction starter can end it.
    if (self::$_transacting > 1) {
      self::$_transacting--;
      return true;
    }
    $db = self::_singleton();
    $retries = 0;
    $errorCode = 5; // Just so we can enter the while loop.
    $query = "END TRANSACTION";
    // SQLITE_BUSY || SQLITE_IOERR_BLOCKED (@see http://www.sqlite.org/c3ref/busy_timeout.html)
    while (self::$_transacting == 1 && ($errorCode == 5 || $errorCode == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES) {
      if ($retries > 0) {
        SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$errorCode}] [ERRMSG={$errorCode}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        sleep(1);
      }
      if (!@$db->exec($query)) {
        SystemEvent::raise(SystemEvent::ERROR, "Could not commit transaction.", __METHOD__);
      } else {
        self::$_transacting--;
        SystemEvent::raise(SystemEvent::DEBUG, "Transaction commited.", __METHOD__);
      }
      $retries++;
      $errorCode = $db->lastErrorCode();
    }
    return (self::$_transacting == 0);
  }

  /**
   * Requests to rollback a transaction are immediately honoured,
   * regardless of who originally started the current transaction.
   */
  static public function rollbackTransaction()
  {
    $db = self::_singleton();
    $retries = 0;
    $errorCode = 5; // Just so we can enter the while loop.
    $query = "ROLLBACK TRANSACTION";
    // SQLITE_BUSY || SQLITE_IOERR_BLOCKED (@see http://www.sqlite.org/c3ref/busy_timeout.html)
    while (self::$_transacting != 0 && ($errorCode == 5 || $errorCode == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES) {
      if ($retries > 0) {
        SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$errorCode}] [ERRMSG={$errorCode}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        sleep(1);
      }
      if (!$db->exec($query)) {
        SystemEvent::raise(SystemEvent::ERROR, "Couldn't rollback transaction.", __METHOD__);
      } else {
        self::$_transacting = 0;
        SystemEvent::raise(SystemEvent::DEBUG, "Transaction rolled back.", __METHOD__);
      }
      $retries++;
      $errorCode = $db->lastErrorCode();
    }
    return (self::$_transacting == 0);
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
    $retries = 0;
    while (!(($stmt = @$db->prepare($query)) instanceof SQLite3Stmt) && ($db->lastErrorCode() == 5 || $db->lastErrorCode() == (10 | (11<<8))) && $retries < CINTIENT_SQL_BUSY_RETRIES) {
      SystemEvent::raise(SystemEvent::NOTICE, "Database is busy, easing off and retrying. [TRIES={$retries}] [ERRNO={$db->lastErrorCode()}] [ERRMSG={$db->lastErrorMsg()}] [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
      sleep(1);
      $retries++;
    }
    if (!($stmt instanceof SQLite3Stmt)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not prepare statement. [QUERY={$query}]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
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
        SystemEvent::raise(SystemEvent::ERROR, "Could not bind value. [QUERY={$query}] [VALUE={$values[$i]}] [TYPE=".print_r($type, true)."]".(!empty($values)?' [VALUES='.(implode(' | ',$values)).']':''), __METHOD__);
        //return false;
      }
    }
    return $stmt;
  }

  static public function getTables()
  {
    $ret = array();
    $sql = 'SELECT tbl_name AS tblname FROM sqlite_master';
    $rs = self::query($sql);
    while ($rs->nextRow()) {
      $ret[$rs->getTblName()] = $rs->getTblName();
    }
    return $ret;
  }

  static public function getTableInfo($tableName)
  {
    $ret = array();
    $sql = "pragma table_info({$tableName})";
    $rs = self::query($sql);
    while ($rs->nextRow()) {
      $ret[$rs->getName()] = $rs->getName();
    }
    return $ret;
  }

  static public function setupTable($tableName, $sqlCreate)
  {
    if (!self::execute($sqlCreate)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems executing table create. This is non recoverable, please fix the problem and try again.", __METHOD__);
      self::rollbackTransaction();
      return false;
    }
    $tables = self::getTables();
    if (!empty($tables[$tableName])) {
      $attributes = Database::getTableInfo($tableName);
      if (empty($attributes)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems accessing old data attributes. This is non recoverable, please fix the problem and try again.", __METHOD__);
        self::rollbackTransaction();
        return false;
      }
      $attributesNew = Database::getTableInfo($tableName . 'NEW');
      if (empty($attributes)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems accessing new data attributes. This is non recoverable, please fix the problem and try again.", __METHOD__);
        self::rollbackTransaction();
        return false;
      }
      //
      // This makes sure that only coincident attributes are corresponded,
      // i.e., old attributes that don't match anything in the current
      // schema, or vice-versa, are left out of the migration
      //
      $attributesSql = '';
      foreach ($attributes as $attribute) {
        if (!empty($attributesNew[$attribute])) {
          $attributesSql .= "{$attribute},";
        }
      }
      $attributesSqlNew = '';
      foreach ($attributesNew as $attributeNew) {
        if (!empty($attributes[$attributeNew])) {
          $attributesSqlNew .= "{$attributeNew},";
        }
      }
      $attributesSql = rtrim($attributesSql, ',');
      $attributesSqlNew = rtrim($attributesSqlNew, ',');

      $sql = "INSERT INTO {$tableName}NEW ({$attributesSqlNew}) SELECT {$attributesSql} FROM $tableName";
      if (!self::execute($sql)) {
        SystemEvent::raise(SystemEvent::ERROR, "Problems migrating old data. This is non recoverable, please fix the problem and try again.", __METHOD__);
        self::rollbackTransaction();
        return false;
      }
    }
    $sql = "DROP TABLE IF EXISTS {$tableName}";
    if (!self::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems removing old data. This is non recoverable, please fix the problem and try again.", __METHOD__);
      self::rollbackTransaction();
      return false;
    }
    $sql = "ALTER TABLE {$tableName}NEW RENAME TO {$tableName}";
    if (!self::execute($sql)) {
      SystemEvent::raise(SystemEvent::ERROR, "Problems setting up migrated data. This is non recoverable, please fix the problem and try again.", __METHOD__);
      self::rollbackTransaction();
      return false;
    }
    return true;
  }
}
