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
 * All AJAX requests are session based, i.e., no GET parameters should be passed
 * for the current user and project.
 * 
 * Mapping rules:
 * 
 * URL => method name => template filename:
 * . /foo         => foo()        => foo.tpl         (default section)
 * . /foo-bar     => fooBar()     => foo-bar.tpl     (default section)
 * . /foo/bar     => foo_bar()    => foo/bar.tpl     (foo section)
 * . /foo/foo-bar => foo_fooBar() => foo/foo-bar.tpl (foo section)
 * 
 */
class AjaxManager
{
  /* +----------------------------------------------------------------+ *\
  |* | DEFAULT                                                        | *|
  \* +----------------------------------------------------------------+ */
  
  static public function triggerBuild()
  {
    if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
      //TODO: error
      exit;
    }
    if (!isset($_SESSION['project']) || !($_SESSION['project'] instanceof Project)) {
      //TODO: error
      exit;
    }
    if ($_SESSION['project']->getUserId() != $_SESSION['user']->getId()) {
      //TODO: not authorized
      exit;
    }
    if ($_SESSION['project']->getAccessLevel() < Access::BUILD) {
      //TODO: not enough perms
      exit;
    }
    if (!$_SESSION['project']->isModified()) {
      //TODO: do nothing
      exit;
    }
    $_SESSION['project']->build();
  }
}