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
 * 
 * Config file section: [svn]
 */
class ScmConnector_Svn implements ScmConnectorInterface
{
  static public function checkout(array $args)
  {
    $command = "svn co --username {$args['username']} --password {$args['password']} {$args['remote']} {$args['local']}";
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
    $command = "svn info --username {$args['username']} --password {$args['password']} {$args['remote']} {$args['local']}";
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
    #if DEBUG
    SystemEvent::raise(SystemEvent::DEBUG, "Repository " . ($matches[1][0]!=$matches[1][1]?'':'not ') . "changed.", __METHOD__);
    #endif
    return ($matches[1][0] != $matches[1][1]);
  }

  static public function update(array $args)
  {
    $command = "svn up --username {$args['username']} --password {$args['password']} {$args['local']}";
    $lastline = exec($command, $output, $return);
    if ($return != 0) {
      $output = implode("\n", $output);
      SystemEvent::raise(SystemEvent::ERROR, "Could not update local working copy. [COMMAND=\"{$command}\"] [RET={$return}] [OUTPUT=\"{$output}\"]", __METHOD__);
      return false;
    }
    return true;
  }
  
  static public function tag(array $args)
  {}
}