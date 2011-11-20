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
 * The Git SCM connector.
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
class ScmConnector_Git implements ScmConnectorInterface
{
  static public function checkout(array $args)
  {
    $command = "git clone {$args['remote']} {$args['local']}";
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
    if (!($localRev = self::_getLocalRevision($args)) || !($remoteRev = self::_getRemoteRevision($args))) {
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Repository " . ($localRev!=$remoteRev?'':'not ') . "changed. [LOCAL={$localRev}] [REMOTE={$remoteRev}]", __METHOD__);
    #endif
    return ($localRev != $remoteRev);
  }


  static private function _getLocalRevision(array $args)
  {
    //
    // Pull up the local revision
    //
    /*
    commit 15ab10edc93ca8a531e750ad300cbf91e5641a7b
    Author: Pedro Mata-Mouros <pedro.matamouros@gmail.com>
    Date:   Sun Sep 18 01:31:31 2011 +0100

    changes to README

    diff --git a/README b/README
    index e69de29..9e3abf0 100644
    --- a/README
    +++ b/README
    @@ -0,0 +1 @@
    +Ola
    \ No newline at end of file
    */
    $command = "git --git-dir={$args['local']}.git show";
    $lastline = exec($command, $output, $return);
    $outputLocal = implode("\n", $output);
    if ($return != 0 || !preg_match('/^commit ([\da-f]{40})$/m', $outputLocal, $matchesLocal)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check local revision. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    return $matchesLocal[1];
  }


  static private function _getRemoteRevision(array $args)
  {
    //
    // Pull up the remote revision
    //
    /*
    From git://github.com/matamouros/Test.git
    0e02ce25ab768f1364575c86e055b89235c64573	HEAD
    0e02ce25ab768f1364575c86e055b89235c64573	refs/heads/master
    */
    $command = "git --git-dir={$args['local']}.git ls-remote";
    $lastline = exec($command, $output, $return);
    $outputRemote = implode("\n", $output);
    if ($return != 0 || !preg_match('/^([\da-f]{40})\s+HEAD$/m', $outputRemote, $matchesRemote)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check remote revision. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    return $matchesRemote[1];
  }


  static public function update(array $args, &$rev)
  {
    $command = "git --git-dir={$args['local']}.git pull";
    $proc = new Framework_Process($command);
    $proc->run();
    if (($return = $proc->getReturnValue()) != 0) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not update local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [STDERR=\"{$proc->getStderr()}\"] [STDOUT=\"{$proc->getStdout()}\"]", __METHOD__);
      return false;
    }
    $rev = self::_getLocalRevision($args);
    SystemEvent::raise(SystemEvent::DEBUG, "Updated local. [REV={$rev}] [COMMAND=\"{$command}\"] [RET={$return}] [STDERR=\"{$proc->getStderr()}\"] [STDOUT=\"{$proc->getStdout()}\"]", __METHOD__);
    return true;
  }


  static public function tag(array $args)
  {}
}