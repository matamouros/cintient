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
class Auth implements AuthInterface
{
  /**
   * On success populates $_SESSION['user'] with the authenticated user.
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
    $_SESSION['user'] = $user;
    SystemEvent::raise(SystemEvent::DEBUG, "User authenticated. [ID={$userId}]", __METHOD__);
    return true;
  }
}