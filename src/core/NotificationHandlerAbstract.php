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

require_once '../lib/lib.htmlgen.php';

/**
 * Abstract class for all Notification handlers to extend.
 *
 * @package     Notification
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
abstract class NotificationHandlerAbstract extends Framework_BaseObject
{
  /**
   * Returns the notification handler corresponding to the current,
   * derived, class.
   */
  public function getHandler()
  {
    return get_class($this);
  }

  /**
   * Returns the HTML code for generating the notification view
   * representation.
   */
  abstract public function getView();

  /**
   * Fires a notification.
   */
  abstract public function fire($msg, $params = array());

  /**
   * States how to consider a child notification class empty.
   */
  abstract public function isEmpty();
}