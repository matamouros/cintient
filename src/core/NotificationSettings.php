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
 * Class for keeping track of notification handler's settings for
 * notification events.
 *
 * @package     Notification
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class NotificationSettings extends Framework_BaseObject
{
  /**
   * An array of settings for each of the notification events, tipically
   * with boolean values, for each of the notification handlers.
   */
  protected $_settings;

  protected $_ptrProject;
  protected $_ptrUser;

  /**
   * Consts for notification events identification.
   */
  const BUILD_FAILED  = 1;
  const BUILD_STARTED = 2;
  const BUILD_SUCCESS = 3;

  /**
   * Array with human description strings for all notification events,
   * for project event notification settings edit purposes.
   */
  static public $eventDescriptions = array(
    self::BUILD_FAILED  => 'On build failed',
    self::BUILD_STARTED => 'On build started',
    self::BUILD_SUCCESS => 'On build success',
  );

  /**
  * Array with human description strings for all notification events,
  * for notification message sending purposes.
  */
  static public $eventMessages = array(
    self::BUILD_FAILED  => 'Build failed!',
    self::BUILD_STARTED => 'Build started.',
    self::BUILD_SUCCESS => 'Build successful.',
  );

  static public $eventDefaults = array(
    self::BUILD_FAILED  => true,
    self::BUILD_STARTED => false,
    self::BUILD_SUCCESS => false,
  );

  /**
   * Sets default values to all notification event types, for each of
   * the available notification handlers.
   */
  public function __construct(Project $project, User $user, $settings = null)
  {
    // TODO: This is a blind attribution... The $settings coming in must
    // be validated...
    if (!empty($settings)) {
      $this->_settings = $settings;
    } else {
      // At instantiation time, always get the freshest notification
      // handlers from the filesystem
      $handlers = self::getHandlers();
      foreach ($handlers as $handler) {
        $this->_settings[$handler] = self::$eventDefaults;
      }
    }
    $this->_ptrProject = $project;
    $this->_ptrUser = $user;
  }

  /**
   * This makes sure that when unserializing a version of this class,
   * the freshest possible handlers/settings are reflected in it, i.e.,
   * only events reflected at that time in this class, and only handlers
   * found at that time in the filesystem, will be allowed in the
   * instance, on unserializing.
   * When added to a Project_User, an instance of this class might never
   * pass through the constructor again, hence the need for this method.
   */
  public function __wakeup()
  {
    $settings = array();
    $handlers = self::getHandlers();
    foreach ($handlers as $handler) {
      $newHandlerSettings = array();
      if (!empty($this->_settings[$handler])) {
        foreach (self::$eventDefaults as $eventKey => $eventDefault) {
          if (isset($this->_settings[$handler][$eventKey])) {
            $newHandlerSettings[$eventKey] = $this->_settings[$handler][$eventKey];
          } else {
            $newHandlerSettings[$eventKey] = $eventDefault;
          }
        }
      } else {
        $newHandlerSettings = self::$eventDefaults;
      }
      $settings[$handler] = $newHandlerSettings;
    }
    $this->_settings = $settings;
  }

  public function getView()
  {
    require_once 'lib/lib.htmlgen.php';
    $o = $this;
    h::table(array('id' => 'projectEditNotifications'), function () use ($o) {
      h::tr(function () use ($o) {
        h::th('');
        foreach ($o->getSettings() as $handler => $_) {
          h::th(substr($handler, strpos($handler, '_')+1)); // Strip the Notification_ part from the class name
        }
      });
      $first = current($o->getSettings());
      foreach ($first as $event => $checked) {
        h::tr(function () use ($o, $event, $checked) {
          h::td(NotificationSettings::$eventDescriptions[$event]);
          foreach ($o->getSettings() as $handler => $settings) {
            h::td(function () use ($o, $event, $handler, $settings) {
              h::div(array('class' => 'checkboxContainer'), function() use ($o, $event, $handler, $settings) {
                $params = array(
                  // Strip the Notification_ part from the class name
                	'name' => $event . '_' . substr($handler, strpos($handler, '_')+1),
                	'type' => 'checkbox',
                );
                if ($settings[$event]) {
                  $params['checked'] = 'checked';
                }
                // If the user's settings don't have this handler configured,
                // then ghost out these per project settings.
                if (!(($handlerObj = $o->getPtrUser()->getActiveNotificationHandler($handler)) instanceof NotificationHandlerAbstract) ||
                      $handlerObj->isEmpty())
                {
                  $params['disabled'] = 'disabled';
                }
                h::input($params);
              });
            });
          }
        });
      }
    });
  }

  /**
   * Returns an array with all the currently available notification
   * handlers. It basically iterates the Notification dir and picks up
   * any available classes there.
   */
  static public function &getHandlers()
  {
    $handlers = array();
/*    //
    // If $this->_settings is empty, go to the filesystem
    //
    if (empty($this->_settings)) {*/
      $dir = CINTIENT_INSTALL_DIR . 'src/core/Notification/';
      foreach (new FilesystemIterator($dir) as $entry) {
        $basename = basename($entry);
        if (strrpos($basename, '.php') !== false) {
          $handlers[] = 'Notification_' . substr($basename, 0, strlen($basename)-4);
        }
      }
    /*} else {
      foreach ($this->_settings as $handler => $settings) {
        $handlers[] = $handler;
      }
    }*/
    sort($handlers);
    return $handlers;
  }
}