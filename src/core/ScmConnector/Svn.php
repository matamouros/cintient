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
 * The SVN SCM connector.
 *
 * @package     Scm
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class ScmConnector_Svn implements ScmConnectorInterface
{
  static public function checkout(array $args)
  {
    $username = '';
    $password = '';
    if (!empty($args['username'])) {
      $username = "--username {$args['username']} ";
      if (!empty($args['password'])) {
        $password = "--password \"{$args['password']}\" ";
      }
    }
    $command = "svn co {$username}{$password}--non-interactive {$args['remote']} {$args['local']}";
    $lastline = exec($command, $output, $return);
    if ($return != 0) {
      $output = implode("\n", $output);
      SystemEvent::raise(SystemEvent::ERROR, "Could not check out remote repository. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    return true;
  }

  static public function isModified(array $args)
  {
    //
    // svn info the local sources
    //
    $username = '';
    $password = '';
    if (!empty($args['username'])) {
      $username = "--username {$args['username']} ";
      if (!empty($args['password'])) {
        $password = "--password \"{$args['password']}\" ";
      }
    }
    $command = "svn info {$username}{$password}--non-interactive {$args['remote']} {$args['local']}";
    $lastline = exec($command, $output, $return);
    $output = implode("\n", $output);
    if ($return != 0) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    if (!preg_match_all('/^Revision: (\d+)$/m', $output, $matches) || !isset($matches[1][0]) || !isset($matches[1][1])) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check for modifications. [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
SystemEvent::raise(SystemEvent::ALERT, print_r($matches, true), __METHOD__);
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Repository " . ($matches[1][0]!=$matches[1][1]?'':'not ') . "changed.", __METHOD__);
    #endif
    return ($matches[1][0] != $matches[1][1]);
  }

  static public function update(array $args, &$rev)
  {
    $username = '';
    $password = '';
    if (!empty($args['username'])) {
      $username = "--username {$args['username']} ";
      if (!empty($args['password'])) {
        $password = "--password \"{$args['password']}\" ";
      }
    }
    $command = "svn up {$username}{$password}--non-interactive {$args['local']}";
    $lastline = exec($command, $output, $return);
    if ($return != 0) {
      $output = implode("\n", $output);
      SystemEvent::raise(SystemEvent::ERROR, "Could not update local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    if (!preg_match('/revision (\d+)/', $lastline, $matches)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not get revision number from update. [COMMAND=\"{$command}\"] [RET={$return}] [LASTLINE=\"{$lastline}\"]", __METHOD__);
      $rev = null;
      return false;
    }
    $rev = (int)$matches[1];
    return true;
  }

  static public function tag(array $args)
  {}
}