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
 * Helper class for handling filesystem operations.
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
class Framework_Filesystem
{
  /**
   * Implements a more powerful filesystem copy method, using the
   * Framework_FilesystemFilterIterator underneath whenever more than a
   * single file is provided for the copy.
   *
   * @see Framework_FilesystemFilterIterator
   *
   *
   * @param string $src The source of the copy operation. If it's a
   * dir, make sure it ends with your system's DIRECTORY_SEPARATOR, or
   * else it will be considered a file.

   * @param string $dst The destination of the copy operation. If it's a
   * dir, make sure it ends with your system's DIRECTORY_SEPARATOR, or
   * else it will be considered a file.
   *
   * @param array $include An array of regexps to match for included
   * entries.
   *
   * @param array $exclude An array of regexps to match for excluded
   * entries.
   *
   * @param int $type FILE|DIR|BOTH specify which type of entry to
   * consider when copying
   *
   * @param bool $defaultExcludes Whether to use the default excludes
   * of @see Framework_FilesystemFilterIterator
   *
   * @return bool true on success, false on failure
   *
   */
  static public function copy($src, $dst, array $include = array('**/*'), array $exclude = array(), $type = Framework_FilesystemFilterIterator::BOTH, $defaultExcludes = true)
  {
    $ret = false;

    if (!file_exists($src)) {
      SystemEvent::raise(SystemEvent::INFO, "Specified source does not exist. [SRC={$src}]", __METHOD__);
      return false;
    }
    $dstDir = $dst;
    if (substr($dst, -1) != DIRECTORY_SEPARATOR) { // If $dst is not a dir
      $dstDir = dirname($dst); // Get the root dir of the specified file
    }
    //
    // Check permissions, if destination exists
    //
    if (file_exists($dstDir)) {
      if (!is_writable($dstDir)) {
        SystemEvent::raise(SystemEvent::INFO, "Destination dir could not be written to. [DST={$dstDir}]", __METHOD__);
        return false;
      }
    //
    // Make sure destination gets created
    //
    } elseif (!@mkdir($dstDir, DEFAULT_DIR_MASK, true)) {
      SystemEvent::raise(SystemEvent::INFO, "Could not create destination dir. [DST={$dstDir}]", __METHOD__);
      return false;
    }
    //
    // Make the destination a filename if $src is a file and if $dst is a dir
    //
    if (is_file($src) && $dstDir == $dst) {
      $dst .= basename($src);
    }
    //
    // If $src is a file, just copy it promptly
    //
    if (is_file($src)) {
      if (!@copy($src, $dst)) {
        SystemEvent::raise(SystemEvent::INFO, "Could not copy file. [SRC={$src}] [DST={$dst}]", __METHOD__);
        return false;
      }
      return true;
    }

    try {
      foreach (new Framework_FilesystemFilterIterator(
                 new RecursiveIteratorIterator(
                   new RecursiveDirectoryIterator($src),
                   RecursiveIteratorIterator::SELF_FIRST, // Because we need to create parent dir before start copying its children
                   RecursiveIteratorIterator::CATCH_GET_CHILD
                 ),
                 $src,
                 $include,
                 $exclude,
                 $type,
                 $defaultExcludes
               ) as $entry)
      {
        $entry = $entry->getRealPath();
        $dest = $dst . substr($entry, strlen($src));
        if ($entry->isFile()) {
          $ret = @copy($entry, $dest);
        } elseif ($entry->isDir()) {
        	if (!file_exists($dest) && !@mkdir($dest, DEFAULT_DIR_MASK, true)) {
        	  return false;
        	} else {
        	  $ret = true;
          }
        } else {
          return false;
        }
      }
    } catch (UnexpectedValueException $e) {
      // Typical permission denied
      SystemEvent::raise(SystemEvent::INFO, "Problems copying. [ENTRY={$entry->getRealPath()}] [MSG={$e->getMessage()}]", __METHOD__);
      return false;
    }
    return $ret;
  }

  /**
   *
   * Enter description here ...
   * @param unknown_type $dir
   */
  static public function emptyDir($dir) {
    $ret = true;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $path) {
      if ($path->isDir()) {
        if ($res = @rmdir($path->__toString())) {
          SystemEvent::raise(SystemEvent::DEBUG, "Removed dir {$path->__toString()}", __METHOD__);
        } else {
          SystemEvent::raise(SystemEvent::ERROR, "Couldn't remove dir {$path->__toString()}", __METHOD__);
        }
        $ret = $ret & $res;
      } else {
        if ($res = @unlink($path->__toString())) {
          SystemEvent::raise(SystemEvent::DEBUG, "Removed file {$path->__toString()}", __METHOD__);
        } else {
          SystemEvent::raise(SystemEvent::ERROR, "Couldn't remove file {$path->__toString()}", __METHOD__);
        }
        $ret = $ret & $res;
      }
    }
    return $ret;
  }

  /**
   *
   * Enter description here ...
   * @param unknown_type $dir
   * @return boolean
   */
  static public function removeDir($dir)
  {
    return (self::emptyDir($dir) && @rmdir($dir));
  }
}