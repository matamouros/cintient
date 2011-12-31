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
class ScmConnector_Git extends ScmConnectorAbstract implements ScmConnectorInterface
{
  public function checkout()
  {
    $command = "{$GLOBALS['settings'][SystemSettings::EXECUTABLE_GIT]} clone {$this->getRemote()} {$this->getLocal()}";
    $proc = new Framework_Process();
    $proc->setExecutable($command);
    $proc->run();
    if (($return = $proc->getReturnValue()) != 0) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check out remote repository. [COMMAND=\"{$command}\"] [RET={$return}] [STDERR=\"{$proc->getStderr()}\"] [STDOUT=\"{$proc->getStdout()}\"]", __METHOD__);
      return false;
    }
    return true;
  }

  /**
   * "Exports" the local working copy to a fresh dir, without any SCM
   * artifacts like .git
   *
   * @see ScmConnectorInterface::export()
   */
  public function export($toDir)
  {
    //TODO: export a tag
    if (!Framework_Filesystem::copy($this->getLocal(), $toDir)) {
      SystemEvent::raise(SystemEvent::INFO, "Could not export local working copy. [LOCAL={$this->getLocal()}] [DIR={$toDir}]", __METHOD__);
    }
    return true;
  }

  public function isModified()
  {
    if (!($localRev = $this->_getLocalRevision()) || !($remoteRev = $this->_getRemoteRevision())) {
      return false;
    }
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Repository " . ($localRev!=$remoteRev?'':'not ') . "changed. [LOCAL={$localRev}] [REMOTE={$remoteRev}]", __METHOD__);
    #endif
    return ($localRev != $remoteRev);
  }

  private function _getLocalRevision()
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
    $command = "{$GLOBALS['settings'][SystemSettings::EXECUTABLE_GIT]} --git-dir={$this->getLocal()}.git show";
    $lastline = exec($command, $output, $return);
    $outputLocal = implode("\n", $output);
    if ($return != 0 || !preg_match('/^commit ([\da-f]{40})$/m', $outputLocal, $matchesLocal)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check local revision. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    return $matchesLocal[1];
  }

  private function _getRemoteRevision()
  {
    //
    // Check locally what is the current branch
    //
    /*
    $ cat HEAD
    ref: refs/heads/develop
    */
    $file = "{$this->getLocal()}.git/HEAD";
    if (!preg_match('/^ref: ([\w\/\-_]+)$/', file_get_contents($file), $matches) || empty($matches[1])) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check local branch on HEAD file. [FILE={$file}] [LOCAL={$this->getLocal()}]", __METHOD__);
      return false;
    }
    $branch = $matches[1];

    //
    // Pull up the remote revision
    //
    /*
    $ git ls-remote git://github.com/matamouros/cintient.git refs/heads/develop
    0fb200fffe72b7d1c4aaf1c0cbd35c0b070b33bb	refs/heads/develop
    */
    $command = "{$GLOBALS['settings'][SystemSettings::EXECUTABLE_GIT]} ls-remote {$this->getRemote()} {$branch}";
    $proc = new Framework_Process();
    $proc->setExecutable($command);
    $proc->run();
    if (($return = $proc->getReturnValue()) != 0 || !preg_match('/^([\da-f]{40})\s+/', $proc->getStdout(), $matchesRemote)) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not check remote revision. [COMMAND=\"{$command}\"] [RET={$return}] [STDERR=\"{$proc->getStderr()}\"] [OUTPUT=\"{$proc->getStdout()}\"]", __METHOD__);
      return false;
    }
    return $matchesRemote[1];
  }

  public function update(&$rev)
  {
    // We can't use "git --git-dir={$this->getLocal()} pull", it's wrong
    $command = "cd {$this->getLocal()} && {$GLOBALS['settings'][SystemSettings::EXECUTABLE_GIT]} pull";
    $proc = new Framework_Process();
    $proc->setExecutable($command, false); // false for no escapeshellcmd() (because of the ';')
    $proc->run();
    if (($return = $proc->getReturnValue()) != 0) {
      SystemEvent::raise(SystemEvent::ERROR, "Could not update local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [STDERR=\"{$proc->getStderr()}\"] [STDOUT=\"{$proc->getStdout()}\"]", __METHOD__);
      return false;
    }
    $rev = $this->_getLocalRevision();
    SystemEvent::raise(SystemEvent::DEBUG, "Updated local. [REV={$rev}] [COMMAND=\"{$command}\"] [RET={$return}] [STDERR=\"{$proc->getStderr()}\"] [STDOUT=\"{$proc->getStdout()}\"]", __METHOD__);
    return true;
  }

  public function tag()
  {}
}