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
 * Notifo.com service notification helper class. Note this isn't a
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
class Notification_Notifo extends NotificationHandlerAbstract
{
  protected $_username; // Notifo username
  protected $_key;      // Notifo API key

  public function __construct()
  {
    $this->_username = '';
    $this->_key = '';
  }

  public function getView()
  {
    $o = $this;
    h::div(array('class' => 'clearfix'), function() use ($o) {
      h::label(array('for' => 'username'), 'API Username');
      h::div(array('class' => 'input'), function() use ($o) {
        h::input(array(
          'class' => 'span6',
          'type'  => 'text',
          'name'  => 'username',
          'value' => $o->getUsername(),
        ));
      });
    });
    h::div(array('class' => 'clearfix'), function() use ($o) {
      h::label(array('for' => 'key'), 'API Secret');
      h::div(array('class' => 'input'), function() use ($o) {
        h::input(array(
          'class' => 'span6',
          'type'  => 'text',
          'name'  => 'key',
          'value' => $o->getKey(),
        ));
      });
    });
  }

  public function fire($msg, $params = array())
  {
    if ($this->isEmpty()) {
      return false;
    }

    include_once 'lib/Notifo_API.php';
    $notifo = new Notifo_API($this->getUsername(), $this->getKey());
    $payload = array(
      'label' => 'Cintient',
      'msg'   => $msg,
    );
    if (!empty($params['uri'])) {
      $payload['uri'] = $params['uri'];
    }
    if (!empty($params['title'])) {
      $payload['title'] = $params['title'];
    }

    $response = $notifo->send_notification($payload);

    // If notifo.com is unreachable (happened before), response is null
    if (is_null($response) || empty($response)) {
      // TODO: Persist these notifications for later retry? Or for
      // showing them to the user on the web page?
      SystemEvent::raise(SystemEvent::INFO, "Notifo seems to be unreachable. [TITLE={$params['title']}] [MSG={$msg}]", __METHOD__);
      return false;
    }
    $response = json_decode($response, true);
    //
    // Check out: https://api.notifo.com/docs/responses
    //
    if (!empty($response['response_code']) && $response['response_code'] == '2201' ) {
      return true;
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Notifo notification returned error. [RSP_CODE={$response['response_code']}] [RSP_MSG={$response['response_message']}]", __METHOD__);
      return false;
    }
  }

  public function isEmpty()
  {
    return (empty($this->_username) || empty($this->_key));
  }
}