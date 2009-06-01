<?php

require_once('PHPUnit/Framework.php');
require_once('TestSetup.php');


  /**
  * @package FrameworkTest
	* @todo add validation tests
  */
  class UpdateTest extends PHPUnit_Framework_TestCase {
	
	
	public function setUp() {
		ActivePhp\Base::$test_mode = true;
		User::start_transaction();
	}
	
	public function testCanUpdateUser() {
		$joe = joe();
		
		$update = User::update($joe->id, array('name' => 'joe_updated'));
		
		$this->assertTrue($update->saved);
		
	}
	
	
	
	public function myIntDataProvider() {
		return array(array(6),array('6'),array("6"));
	}
	
	
	/**
	 * @dataProvider myIntDataProvider
	 */
	public function testCanUpdateUserMyint($int) {
		$joe = joe();
		$update = User::update($joe->id, array('my_int' => $int));
		$this->assertTrue($update->saved);
		$this->assertEquals($update->my_int, $int);
	}


	public function testCanUpdateUserMyintNonNumeric() {
		$joe = joe();
		$update = User::update($joe->id, array('my_int' => 'l'));
		$this->assertFalse($update->saved);
		//reload joe
		$joe2 = User::_find($joe->id);
		//assert that nothing changed
		$this->assertEquals($joe2->my_int, $joe->my_int);
	}
	
	
	
	public function tearDown() {
		User::end_transaction();
		ActivePhp\Base::$test_mode = flase;
	}
	
	
	}
	
?>