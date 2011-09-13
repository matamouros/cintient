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
 * Class for handling redirects.
 *
 * @package     Global
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
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
      SystemEvent::raise(SystemEvent::INFO, "Not found. [URI={$GLOBALS['uri']}] [USER=" . (($GLOBALS['user'] instanceof User)? $GLOBALS['user']->getUsername() : 'N/A') . ']');
      header('Location: ' . CINTIENT_BASE_URL . '/not-found/', true, 404);
    } elseif (self::NOT_AUTHORIZED) {
      SystemEvent::raise(SystemEvent::INFO, "Not authorized. [URI={$GLOBALS['uri']}] [USER=" . (($GLOBALS['user'] instanceof User)? $GLOBALS['user']->getUsername() : 'N/A') . ']');
      header('Location: ' . CINTIENT_BASE_URL . '/not-authorized/', true, 403);
    }
    exit;
  }

  public static function redirectToUri($url)
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Redirecting from [URI={$GLOBALS['uri']}] to [URL={$url}]");
    header('Location: ' . $url);
    exit;
  }
}
?>
