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
 * A class for storing all of the platform's sort criteria, for easy and
 * centralized access.
 *
 * @package Reference
 */
class Sort
{
  const NONE = 0;
  const ALPHA_ASC = 1;  // A -> Z
  const ALPHA_DESC = 2; // Z -> A
  const DATE_ASC = 4;   // Older -> newer
  const DATE_DESC = 8;  // Newer -> older
}