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

require_once dirname(__FILE__) . '/../config/phpunit.conf.php';

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