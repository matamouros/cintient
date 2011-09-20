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
    if ($return != 0) {
      //SystemEvent::raise(SystemEvent::ERROR, "Could not check local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    if (!preg_match('/^commit ([\da-f]{40})$/m', $outputLocal, $matchesLocal)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check for modifications. [OUTPUT=\"{$outputLocal}\"]", __METHOD__);
      return false;
    }

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
    if ($return != 0) {
      //SystemEvent::raise(SystemEvent::ERROR, "Could not check local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    if (!preg_match('/^([\da-f]{40})\s+HEAD$/m', $outputRemote, $matchesRemote)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check for modifications. [OUTPUT=\"{$outputRemote}\"]", __METHOD__);
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Repository " . ($matchesRemote[1]!=$matchesLocal[1]?'':'not ') . "changed.", __METHOD__);
    #endif
    return ($matchesRemote[1] != $matchesLocal[1]);
  }

  static public function update(array $args, &$rev)
  {
    // --git-dir gives out a permission denied... (??)
    //$command = "git --git-dir={$args['local']} pull";
    $command = "cd {$args['local']}; git pull";// &> /tmp/wtf.txt";
    $lastline = exec($command, $output, $return);
    if ($return != 0) {
      $output = implode("\n", $output);
      SystemEvent::raise(SystemEvent::ERROR, "Could not update local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    // Pull up the revision digest
    $command = "git --git-dir={$args['local']}.git ls-remote";
    $lastline = exec($command, $output, $return);
    $output = implode("\n", $output);
    if ($return != 0 || !preg_match('/^([\da-f]{40})\s+HEAD$/m', $output, $matches)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not get revision number from update. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
    }
    $rev = $matches[1];
    return true;
  }

  static public function tag(array $args)
  {}
}