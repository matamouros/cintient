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


// TODO: centralize the initalization stuff that is common to web, ajax
// and builder handlers. (this builder could be called by a crontab)
require_once dirname(__FILE__) . '/../config/cintient.conf.php';
SystemEvent::setSeverityLevel(CINTIENT_LOG_SEVERITY);

sleep(5); // defer it a little, to give it's [possibly] web-request-father process a chance to go away fast.
do {
  $projects = Project::getNextToBuild();
  foreach ($projects as &$project) {
    SystemEvent::raise(SystemEvent::INFO, "Starting project build. [PID={$project->getId()}]", 'runBuildWorker');
    if (!$project->build()) {
      SystemEvent::raise(SystemEvent::INFO, "Project not built. [PID={$project->getId()}]", 'runBuildWorker');
      // Could be due to the project not having been initialized, or
      // currently building, etc. This sleep() is the easiest way to
      // make sure that the runBuilderWorker is not force trying to
      // build a "unbuildable" (even if temporary) single project.
      sleep(60);
    } else {
      SystemEvent::raise(SystemEvent::INFO, "Project built. [PID={$project->getId()}]", 'runBuildWorker');
    }
    $project = null;
    unset($project);
    // Take the chance to look if we're close to breaking the mem limit
    if (memory_get_usage(true) > ((int)(Utility::phpIniSizeToBytes(ini_get('memory_limit'))*0.9))) {
      SystemEvent::raise(SystemEvent::WARNING, "Getting close to system memory usage hard limit. Shutting down gracefully, while we can. [MEM_USAGE=" . Utility::bytesToHumanReadable(memory_get_usage(true)) . "] [MEM_PEAK=" . Utility::bytesToHumanReadable(memory_get_peak_usage(true)) . "]", __METHOD__);
      exit(0);
    }
  }
  // Rest a bit...
  if (empty($projects)) {
    SystemEvent::raise(SystemEvent::DEBUG, "No projects to build for now. Sleeping...", 'runBuildWorker');
    sleep(60);
  }
  $projects = null;
  unset($projects);

  // Force garbage collection. Project wasn't being updated (status) at
  // the end, without this.
  gc_collect_cycles();

  SystemEvent::raise(SystemEvent::DEBUG, "Memory usage stats [MEM_USAGE=" . Utility::bytesToHumanReadable(memory_get_usage(true)) . "] [MEM_PEAK=" . Utility::bytesToHumanReadable(memory_get_peak_usage(true)) . "]", 'runBuildWorker');
} while (true);

exit;
