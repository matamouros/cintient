<?php
/*
 * 
 * Cintient, Continuous Integration made simple.
 * Copyright (c) 2011, Pedro Mata-Mouros Fonseca
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 
 * . Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * 
 * . Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following
 *   disclaimer in the documentation and/or other materials provided
 *   with the distribution.
 *   
 * . Neither the name of Pedro Mata-Mouros Fonseca, Cintient, nor
 *   the names of its contributors may be used to endorse or promote
 *   products derived from this software without specific prior
 *   written permission.
 *   
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 */

/**
 * 
 */
class XmlBuilderElement
{
  private $_xml;
  
  public function __construct()
  {
    $this->_xml = new XMLWriter();
    $this->_xml->openMemory();
    //$this->_xml->startDocument(); // this hack avoids the output of the <?xml version="1.0" element
    $this->_xml->setIndent(true);
    $this->_xml->setIndentString('  ');
  }
  
  public function __destruct()
  {
    $this->_xml = null;
    unset($this->_xml);
  }
  
  public function __call($name, $args)
  {
    if (method_exists($this->_xml, $name)) {
      if (empty($args)) {
        return $this->_xml->$name();
      } elseif (count($args) == 1) {
        return $this->_xml->$name($args[0]);
      } elseif (count($args) == 2) {
        return $this->_xml->$name($args[0], $args[1]);
      } else {
        trigger_error("Invalid arguments specified for called method", E_USER_ERROR);
        exit;
      }
    } else {
      trigger_error("No valid method available for calling", E_USER_ERROR);
      exit;
    }
  }
  
  public function flush()
  {
    $this->_xml->endDocument();
    return $this->_xml->flush();
  }
}