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
 * Email notifications helper class. Note this isn't a
 * DatabaseObject, i.e., it's not itself responsible for keeping itself
 * persistent. It merely takes a given instantiation and triggers
 * notifications with it.
 *
 * @package     Notification
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Notification_Email extends NotificationAbstract
{
  protected $_emails;

  public function __construct()
  {
    $this->_emails = array();
  }

  public function getView()
  {
    $o = $this;
    h::div(array('class' => 'label'), 'Addresses <span class="fineprintLabel">(one on each line)</span>');
    h::div(array('class' => 'textareaContainer'), function() use ($o) {
      h::textarea(array('name' => 'emails'), implode(PHP_EOL, $o->getEmails()));
    });
  }

  public function setEmails($emails)
  {
    if (is_array($emails)) {
      $this->_emails = $emails;
    } else {
      $this->_emails = explode(PHP_EOL, $emails);
    }
  }

  public function fire($msg, $params = array())
  {
    SystemEvent::raise(SystemEvent::DEBUG, "Email sent!", __METHOD__);
    return true;
  }

  public function isEmpty()
  {
    return empty($this->_emails);
  }
}