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
 * The $args array used throughout this interface should have the following
 * placeholders:
 * 
 * . remote   => the remote repository
 * . local    => the local working copy to act upon
 * . username => the authorized username to interact with the repository
 * . password => the password of the authorized username
 */
interface ScmConnectorInterface
{
  static public function checkout(array $args);
  
  static public function isModified(array $args);

  static public function update(array $args, &$rev);
  
  static public function tag(array $args);
}