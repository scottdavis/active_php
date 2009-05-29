<?php

class DataTestCase extends PHPUnit_Framework_TestCase {
	
	
	public function setUp() {
		ActivePhp\Base::$test_mode = true;
		User::start_transaction();
	}
	
	public function testFoo() {
		$this->assertTrue(true);
	}
	
	public function tearDown() {
		User::end_transaction();
		ActivePhp\Base::$test_mode = false;
	}
	
}

?>