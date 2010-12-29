<?php
/*
 * Cintient, Continuous Integration made simple.
 * 
 * Copyright (c) 2011, Pedro Mata-Mouros <pedro.matamouros@gmail.com>
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

class DatabaseTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $sql = <<<EOT
DROP TABLE IF EXISTS tests;
CREATE TABLE tests ( 
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  fieldvarchar VARCHAR(255) DEFAULT '',
  fieldint INT DEFAULT 0
);
EOT;
    Database::execute($sql);
  }
  
  public function tearDown()
  {
    $sql = 'DROP TABLE IF EXISTS tests';
    Database::execute($sql);
  }
  
  /**
   * Tests Database::execute($sql), with only one argument, i.e., with no
   * variable bindings 
   * 
   * @dataProvider provider
   */
  public function testExecuteNoParamsBinding($a,$b,$c)
  {
    $sql = "INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (".$a.",'".$b."',".$c.")";
    $rs = Database::execute($sql);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    
    $sql = "SELECT id,fieldvarchar,fieldint FROM tests WHERE id=".$a;
    $rs = Database::query($sql);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
  /**
   * Tests Database::execute($sql,$values), with two arguments, i.e., with
   * variable bindings
   * 
   * @dataProvider provider
   */
  public function testExecuteParamsBinding($a,$b,$c)
  {
    $sql = 'INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (?,?,?)';
    $values = array($a,$b,$c);
    $rs = Database::execute($sql,$values);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    
    $sql = "SELECT id,fieldvarchar,fieldint FROM tests WHERE id=".$a;
    $rs = Database::query($sql);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
  /**
   * Tests Database::insert($sql), with only one argument, i.e., with no
   * variable bindings
   * 
   * @dataProvider provider
   */
  public function testInsertNoParamsBinding($a,$b,$c)
  {
    $sql = "INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (".$a.",'".$b."',".$c.")";
    $rs = Database::insert($sql);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $rs, 'No last ID returned after insert');
    
    $sql = "SELECT id,fieldvarchar,fieldint FROM tests WHERE id=".$a;
    $rs = Database::query($sql);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
  /**
   * Tests Database::insert($sql,$values), with two arguments, i.e., with
   * variable bindings
   * 
   * @dataProvider provider
   */
  public function testInsertParamsBinding($a,$b,$c)
  {
    $sql = 'INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (?,?,?)';
    $values = array($a,$b,$c);
    $rs = Database::insert($sql,$values);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_INT, $rs, 'No last ID returned after insert');
    
    $sql = "SELECT id,fieldvarchar,fieldint FROM tests WHERE id=".$a;
    $rs = Database::query($sql);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
/**
   * Tests Database::query($sql), with only one argument, i.e., with no
   * variable bindings
   * 
   * @dataProvider provider2
   */
  public function testQueryNoParamsBinding($a,$b,$c)
  {
    $sql = "INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (".$a.",'".$b."',".$c.")";
    $rs = Database::insert($sql);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    
    $sql = "SELECT id,fieldvarchar,fieldint FROM tests WHERE id=".$a;
    $rs = Database::query($sql);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
  /**
   * Tests Database::query($sql, $values), with two arguments, i.e.,
   * with variable bindings
   * 
   * @dataProvider provider2
   */
  public function testQueryParamsBinding($a,$b,$c)
  {
    $sql = 'INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (?,?,?)';
    $values = array($a,$b,$c);
    $rs = Database::insert($sql, $values);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    
    $sql = 'SELECT id,fieldvarchar,fieldint FROM tests WHERE id=? AND fieldvarchar=? AND fieldint=?';
    $values = array($a,$b,$c);
    $rs = Database::query($sql,$values);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
  /**
   * Tests Database::query($sql,$values), with two arguments, i.e., with
   * variable bindings and more values than placeholders in the query.
   * 
   * @dataProvider provider2
   */
  public function testQueryParamsBindingExcessValues($a,$b,$c) {
    $sql = 'INSERT INTO tests (id,fieldvarchar,fieldint) VALUES (?,?,?)';
    $values = array($a,$b,$c);
    $rs = Database::insert($sql,$values);
    $this->assertTrue($rs!==false, 'INSERT failed!');
    
    $sql = 'SELECT id,fieldvarchar,fieldint FROM tests WHERE id=?';
    $values = array($a,$b);
    $rs = Database::query($sql,$values);
    $this->assertTrue(($rs!==false&&$rs->nextRow()), 'SELECT failed!');
    $this->assertEquals(array($a,$b,$c), array($rs->getId(), $rs->getFieldVarchar(), $rs->getFieldInt()), 'Database contents incorrect');
  }
  
  public function provider() {
    return array(
      array(1, '1iibGKKA8WB1YJcODqPBZhKCUIa', 1234),
      array(2, '2VkvSKqnvAUcTe0fjjhT5PdpMJd', 2345),
      array(3, '3XFDLJO9nukDLFTukiRgDP7CsbK', 3456),
      array(4, '4Bni9qgJQgiAKwNmbxNCGFHww7x', 4567),
      array(5, '5CrdPZlZyQqZwXEdM6sxicM6BWB', 5678)
    );
  }
  
  public function provider2() {
    return array(
      array(10,'10YzFOcMz6Ohl4qESnYKbwjPffQ', 1011)
    );
  }
}