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
 * Class that implements a local database authentication.
 *
 * @package     Authentication
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Auth_Local implements AuthInterface
{
  static public function authenticate()
  {
    $userId = false;
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['username']) || !isset($_POST['password'])) {
      return null;
    }
    $sql = 'SELECT u.id FROM user u, userauth ua'
         . ' WHERE u.username=?'
         . ' AND ua.password=?'
         . ' AND u.id=ua.userid';
    $values = array($_POST['username'], hash('sha256', PASSWORD_SALT . $_POST['password']));
    $rs = Database::query($sql, $values);
    if ($rs->nextRow()) {
      return $rs->getId();
    } else {
      return false;
    }
  }
}