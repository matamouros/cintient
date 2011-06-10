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
 * Class for handling registration of Smarty plugins.
 *
 * @package     Framework
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class Framework_SmartyPlugin
{
  /**
   * Call this for easy setup and registration of all necessary custom
   * Smarty plugins.
   *
   * @param Smarty $smarty The smarty object on which to register the
   * plugins.
   */
  static public function init($smarty)
  {
    // Register plugin for HTML friendly raw output from builds.
    // Use registerPlugin() on an earlier Smarty version.
    $smarty->register->modifier('raw2html', array('Framework_SmartyPlugin', 'raw2html'));
  }

  /**
   * Method for turning a raw Cintient output into an HTML friendly
   * string, i.e., with spaces replaced by '&nbsp;' and newlines by
   * '<br/>' entity.
   *
   * @param string $str The raw text string
   * @return string An HTML friendly string
   */
  static public function raw2html($str)
  {
    return str_replace(array(' ', PHP_EOL), array('&nbsp;', '<br/>'), $str);
  }
}