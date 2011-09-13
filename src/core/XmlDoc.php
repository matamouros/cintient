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
 * XML document builder helper class, to help out all XML exportation
 * needs. This class maps XMLWriter's methods directly, so that its
 * usage is basically the same as if you
 *
 * @package     Utility
 * @author      Pedro Mata-Mouros Fonseca <pedro.matamouros@gmail.com>
 * @copyright   2010-2011, Pedro Mata-Mouros Fonseca.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU GPLv3 or later.
 * @version     $LastChangedRevision$
 * @link        $HeadURL$
 * Changed by   $LastChangedBy$
 * Changed on   $LastChangedDate$
 */
class XmlDoc extends XmlWriter
{
  public function __construct()
  {
    parent::openMemory();
    //parent::startDocument(); // this hack avoids the output of the <?xml version="1.0" element
    parent::setIndent(true);
    parent::setIndentString('  ');
  }

  /**
   * The parameter is merely to comply with PHP's notion that I am
   * overriding the parent's flush, so I should provide the same
   * signature. Nor am I not, but, even if I was, PHP should allow
   * different signatures, i.e., enable both methods to be called...
   *
   * @see XMLWriter::flush()
   */
  public function flush($empty = true)
  {
    parent::endDocument();
    return parent::flush($empty);
  }
}