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
 * 
 */
class Auth implements AuthInterface
{
  /**
   * On success populates $GLOBALS['user'] with the authenticated user.
   * 
   * @return bool 
   */
  static public function authenticate()
  {
    $authClass = 'Auth_' . ucfirst(AUTH_METHOD);
    if (!class_exists($authClass)) {
      SystemEvent::raise(SystemEvent::ERROR, 'Authentication module not found, reverting to default local. [MODULE=' . AUTH_METHOD . '] [PATH="src/core/Auth/' . $authClass . '.php"]', __METHOD__);
      return false;
    }
    $userId = $authClass::authenticate();
    if (null === $userId) {
      // No actual authentication attempt was tried, so no point in logging anything
      SystemEvent::raise(SystemEvent::DEBUG, "No authentication attempted.", __METHOD__);
      return null;
    } elseif (false === $userId) {
      SystemEvent::raise(SystemEvent::DEBUG, "Failed authentication attempt.", __METHOD__);
      return false;
    }
    $user = User::getById($userId);
    if (!($user instanceof User)) {
      SystemEvent::raise(SystemEvent::INFO, "Unknown user from user ID. [ID={$userId}]", __METHOD__);
      return false;
    }
    $_SESSION['userId'] = $userId;
    $GLOBALS['user'] = $user;
    SystemEvent::raise(SystemEvent::DEBUG, "User authenticated. [USR={$user->getUsername()}]", __METHOD__);
    return true;
  }
  
  static public function logout()
  {
    $username = $GLOBALS['user']->getUsername();
    $GLOBALS['user'] = null;
    unset($GLOBALS['user']);
    session_destroy();
    SystemEvent::raise(SystemEvent::DEBUG, "User logged out. [ID={$username}]", __METHOD__);
    return true;
  }
}