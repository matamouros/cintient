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

/**
 * Interface that specifies a special task type. Special tasks can define
 * specific callbacks that are invoked on key moments of the integration
 * building process. They tipically also envolve changes and customizations
 * to the building results interface.
 *
 * @package     Build
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
interface Build_SpecialTaskInterface
{
  public function __construct(Project_Build $build);

  /**
   * The preBuild() method is invoked right before the integration
   * builder is run.
   */
  public function preBuild();

  /**
   * The postBuild() method is invoked right after the integration
   * builder is run successfully.
   */
  public function postBuild();

  /**
   * This is used to provide whatever data structures are required to
   * render the special task's output in the UI.
   */
  public function getViewData(Array $params = array());

  /**
   * Gets a specific special task object.
   *
   * @param Project_Build $build
   * @param User $user
   * @param int $access
   * @param array $options
   */
  static public function getById(Project_Build $build, User $user, $access = Access::READ, array $options = array());
}