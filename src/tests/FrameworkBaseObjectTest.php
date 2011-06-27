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

class FrameworkBaseObjectTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->sharedFixture = new FrameworkBaseObjectTestHelper();
  }

  /**
   * @expectedException PHPUnit_Framework_Error
   */
  public function testPrivateGetAccessors()
  {
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR3, $this->sharedFixture->getPrivateNonUnderscoredFoo(), 'get acessor failed');
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR6, $this->sharedFixture->getPrivateUnderscoredFoo(), 'get acessor failed');
  }

	/**
   * @expectedException PHPUnit_Framework_Error
   */
  public function testPublicAndProtectedNonUnderscoredGetAccessors()
  {
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR1, $this->sharedFixture->getPublicNonUnderscoredFoo(), 'get acessor failed');
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR2, $this->sharedFixture->getProtectedNonUnderscoredFoo(), 'get acessor failed');
  }

	/**
   *
   */
  public function testPublicAndProtectedUnderscoredGetAccessors()
  {
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR4, $this->sharedFixture->getPublicUnderscoredFoo(), 'get acessor failed');
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR5, $this->sharedFixture->getProtectedUnderscoredFoo(), 'get acessor failed');
  }

	/**
   * TODO: Setting *existing* private attributes Fatal errors. Way to have PHPUnit test this?
   */
  /*public function testPrivateNonUnderscoredSetAccessors()
  {
    $this->assertFailure($this->sharedFixture->setPrivateNonUnderscoredFoo(FrameworkBaseObjectTestHelper::BAR6));
  }*/

	/**
   * TODO: Setting *existing* private attributes Fatal errors. Way to have PHPUnit test this?
   */
  /*public function testPrivateUnderscoredSetAccessors()
  {
    $this->sharedFixture->setPrivateUnderscoredFoo(FrameworkBaseObjectTestHelper::BAR3);
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR3, $this->sharedFixture->getPrivateUnderscoredFoo(), 'Set acessor failed');
  }*/

	/**
   *
   */
  public function testPublicAndProtectedNonUnderscoredSetAccessors()
  {
    $this->sharedFixture->setPublicNonUnderscoredFoo(FrameworkBaseObjectTestHelper::BAR2);
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR2, $this->sharedFixture->getPublicNonUnderscoredFoo(), 'Set acessor failed');
    $this->sharedFixture->setProtectedNonUnderscoredFoo(FrameworkBaseObjectTestHelper::BAR1);
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR1, $this->sharedFixture->getProtectedNonUnderscoredFoo(), 'Set acessor failed');
  }

	/**
   *
   */
  public function testPublicAndProtectedUnderscoredSetAccessors()
  {
    $this->sharedFixture->setPublicUnderscoredFoo(FrameworkBaseObjectTestHelper::BAR5);
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR5, $this->sharedFixture->getPublicUnderscoredFoo(), 'Set acessor failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(FrameworkBaseObjectTestHelper::BAR4);
    $this->assertSame(FrameworkBaseObjectTestHelper::BAR4, $this->sharedFixture->getProtectedUnderscoredFoo(), 'Set acessor failed');
  }

  /**
   * Trying to access an unexisting attribute results in an error.
   *
   * @expectedException PHPUnit_Framework_Error
   */
  public function testGetUnexistingAttribute()
  {
    $this->sharedFixture->getWhateverDude();
  }

	/**
   * Setting an unexisting attribute, creates it on-the-fly. It is then
   * ready to be accessed.
   */
  public function testSetUnexistingAttribute()
  {
    $this->sharedFixture->setWhateverDude(123);
    $this->assertSame(123, $this->sharedFixture->getWhateverDude());
  }

	/**
   * Is accessord should *always* return boolean types and deal with
   * automatic casting.
   */
  public function testIsAccessors()
  {
    $this->sharedFixture->setProtectedUnderscoredFoo(null);
    $this->assertSame(false, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for null failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(0);
    $this->assertSame(false, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for 0 failed');
    $this->sharedFixture->setProtectedUnderscoredFoo('');
    $this->assertSame(false, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for \'\' failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(array());
    $this->assertSame(false, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for array() failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(false);
    $this->assertSame(false, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for false failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(1);
    $this->assertSame(true, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for 1 failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(array(''));
    $this->assertSame(true, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for array(\'\') failed');
    $this->sharedFixture->setProtectedUnderscoredFoo('greetings');
    $this->assertSame(true, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for \'greetings\' failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(array('greetings'));
    $this->assertSame(true, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for array(\'greetings\') failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(array(''));
    $this->assertSame(true, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for array(\'\') failed');
    $this->sharedFixture->setProtectedUnderscoredFoo(true);
    $this->assertSame(true, $this->sharedFixture->isProtectedUnderscoredFoo(), 'is acessor for true failed');
  }
}

class FrameworkBaseObjectTestHelper extends Framework_BaseObject
{
  public     $publicNonUnderscoredFoo;
  protected  $protectedNonUnderscoredFoo;
  private    $privateNonUnderscoredFoo;
  public     $_publicUnderscoredFoo;
  protected  $_protectedUnderscoredFoo;
  private    $_privateUnderscoredFoo;

  const BAR1 = 'bar1';
  const BAR2 = 'bar2';
  const BAR3 = 'bar3';
  const BAR4 = 'bar4';
  const BAR5 = 'bar5';
  const BAR6 = 'bar6';

  public function __construct()
  {
    $this->publicNonUnderscoredFoo    = self::BAR1;
    $this->protectedNonUnderscoredFoo = self::BAR2;
    $this->privateNonUnderscoredFoo   = self::BAR3;
    $this->_publicUnderscoredFoo      = self::BAR4;
    $this->_protectedUnderscoredFoo   = self::BAR5;
    $this->_privateUnderscoredFoo     = self::BAR6;
  }
}