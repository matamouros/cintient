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

// TODO: centralize the initalization stuff that is common to web, ajax
// and builder handlers. (this builder could be called by a crontab)
require_once dirname(__FILE__) . '/../config/cintient.conf.php';
SystemEvent::setSeverityLevel(CINTIENT_LOG_SEVERITY);
$GLOBALS['settings'] = SystemSettings::load(); // Pull up system settings

// Temporarily disable the background build handler in Windows, while
// binaries aren't being dealt with in the installer.
if (Framework_HostOs::isWindows()) {
  SystemEvent::raise(SystemEvent::INFO, "Background builds are temporarily disabled in Windows.", 'buildHandler');
  return false;
}
$buildProcess = new Framework_Process($GLOBALS['settings'][SystemSettings::EXECUTABLE_PHP]);
$buildProcess->addArg(CINTIENT_INSTALL_DIR . 'src/workers/runBuildWorker.php');
if (!$buildProcess->isRunning()) {
  if (!$buildProcess->run(true)) {
    SystemEvent::raise(SystemEvent::ERROR, "Problems starting up build worker.", 'buildHandler');
  } else {
    SystemEvent::raise(SystemEvent::INFO, "Build worker left running in the background.", 'buildHandler');
  }
}
#if DEBUG
else {
  SystemEvent::raise(SystemEvent::DEBUG, "Build process already running.", 'buildHandler');
}
#endif
