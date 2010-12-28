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
 * Class for handling redirects.
 */
class Redirector
{
  /**
   * HTTP 404 - Not Found
   */
  const NOT_FOUND = 404;
  
  /**
   * HTTP 403 - Not Authorized
   */
  const NOT_AUTHORIZED = 403;
  
  /**
   * Redirects by means of setting an HTTP header Location.
   * 
   * @param int $code The code of the redirection, for internal routing.
   */
  public static function redirectAndExit($code)
  {
    if (self::NOT_FOUND) {
      SystemEvent::raise(SystemEvent::INFO, "Not found. [URI={$GLOBALS['uri']}] [USER=" . (($_SESSION['user'] instanceof User)? $_SESSION['user']->getUsername() : 'N/A') . ']');
      header('Location: http://' . SERVER . '/not-found/', true, 404);
    } elseif (self::NOT_AUTHORIZED) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [URI={$GLOBALS['uri']}] [USER=" . (($_SESSION['user'] instanceof User)? $_SESSION['user']->getUsername() : 'N/A') . ']');
      header('Location: http://' . SERVER . '/not-authorized/', true, 403);
    }
    exit;
  }
  
  public static function redirectToUri($url)
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Redirecting from [URI={$GLOBALS['uri']}] to [URI={$uri}]");
    header('Location: ' . $url);
    exit;
  }
}
?>
