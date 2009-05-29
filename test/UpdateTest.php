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
		return array(array(6),array('6'),array("6"),array('l'));
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

	
	
	
	public function tearDown() {
		User::end_transaction();
		ActivePhp\Base::$test_mode = flase;
	}
	
	
	}
	
?>