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
 * 
 */
class UrlManager
{
  static public function getForAjaxAvatarUpload()
  {
    return CINTIENT_BASE_URL . '/ajax/avatar-upload/'; 
  }
  
  static public function getForAjaxProjectBuild()
  {
    return CINTIENT_BASE_URL . '/ajax/project/build/';
  }
  
  static public function getForAsset($filename, $params = array())
  {
    $params['f'] = $filename;
    return CINTIENT_BASE_URL . "/asset/?" .  http_build_query($params);
  }
  
  static public function getForAuthentication()
  {
    return CINTIENT_BASE_URL . '/authentication/';
  }
  
  static public function getForDashboard()
  {
    return CINTIENT_BASE_URL . '/dashboard/';
  }
  
  static public function getForProjectBuildHistory()
  {
    return CINTIENT_BASE_URL . '/project/history/';   
  }
  
  static public function getForProjectBuildView(Project $project, ProjectBuild $build)
  {
    return CINTIENT_BASE_URL . "/project/history/?pid={$project->getId()}&bid={$build->getId()}";
  }
  
  static public function getForProjectEdit()
  {
    return CINTIENT_BASE_URL . '/project/edit/';
  }
  
  static public function getForProjectEditUsers()
  {
    return CINTIENT_BASE_URL . '/project/edit/?users';
  }
  
  static public function getForProjectNew()
  {
    return CINTIENT_BASE_URL . '/project/new/';
  }
  
  static public function getForProjectView(Project $project = null)
  {
    $param = '';
    if ($project instanceof Project) {
      $param = "?pid={$project->getId()}";
    }
    return CINTIENT_BASE_URL . "/project/" . $param;
  }
}