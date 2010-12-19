<?php
/*
 * 
 * Cintient, Continuous Integration made simple.
 * Copyright (c) 2011, Pedro Mata-Mouros Fonseca
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

error_reporting(-1);
define('LOG_FILE', '/var/log/cintient.log');
define('WORK_DIR', '/var/run/cintient/');
define('LOCAL_WORKING_COPY_DIR', WORK_DIR . 'local/');

define('DEFAULT_DIR_MASK', 0777);

define('INSTALL_DIR', '/Users/pfonseca/Dev/cintient/');

define('AUTH_METHOD', 'local'); // Taken from src/core/Auth/

define('SCM_DEFAULT_CONNECTOR', 'svn'); // Taken from src/core/ScmConnector/

define('SMARTY_DEBUG', false);
define('SMARTY_FORCE_COMPILE', false);
define('SMARTY_COMPILE_CHECK', true);
define('SMARTY_TEMPLATE_DIR', INSTALL_DIR . 'src/templates/');
define('SMARTY_COMPILE_DIR', '/tmp/');

define('PASSWORD_SALT', 'rOTA4spNYI3yXvAL');

define('SERVER', 'localhost');

define('DATABASE_FILE', '/var/run/cintient/cintient.sqlite');

//
// The following is a workaround on the fact that the translation of this
// serialized object to the database gets all broken, due to the fact of PHP
// introducing NULL bytes around the '*' that is prepended before protected
// variable members, in the serialized mode. This method replaces those
// problematic NULL bytes with an identifier string '==',
// rendering serialization and unserialization of these specific kinds of
// object safe. Credits to travis@travishegner.com on:
// http://pt.php.net/manual/en/function.serialize.php#96504
//
define('CINTIENT_NULL_BYTE_TOKEN', '==');

define('CINTIENT_PHP_BINARY', 'php');
