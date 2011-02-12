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


require_once dirname(__FILE__) . '/../config/cintient.conf.php';

sleep(5); // defer it a little, to give it's [possibly] web-request-father process a chance to go away fast.
do {
  $project = Project::getNextToBuild();
  if ($project instanceof Project) {
    SystemEvent::raise(SystemEvent::INFO, "Starting project build. [PID={$project->getId()}]", 'runBuildWorker');
    if (!$project->build()) {
      SystemEvent::raise(SystemEvent::INFO, "Project build failed. [PID={$project->getId()}]", 'runBuildWorker');
      $project->log("Building failed.");
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Project build successful. [PID={$project->getId()}]", 'runBuildWorker');
      $project->log("Building successful.");
    }
  } else {
    SystemEvent::raise(SystemEvent::DEBUG, "No projects to build for now. Sleeping...", 'runBuildWorker');
    sleep(60);
  }
  $project = null;
  unset($project);
  SystemEvent::raise(SystemEvent::DEBUG, "Memory usage stats [MEM_USAGE=" . Utility::bytesToHumanReadable(memory_get_usage(true)) . "] [MEM_PEAK=" . Utility::bytesToHumanReadable(memory_get_peak_usage(true)) . "]", 'runBuildWorker');
} while (true);

exit;
