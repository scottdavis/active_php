<?php

abstract class DataTestCase extends PHPUnit_Framework_TestCase {
	
	
	public function setUp() {
		ActivePhp\Base::$test_mode = true;
		User::start_transaction();
		if(method_exists($this, 'setup')) {
			call_user_func(array($this, 'setup'));
		}
	}
	
	public function tearDown() {
		if(method_exists($this, 'teardown')) {
			call_user_func(array($this, 'teardown'));
		}
		User::end_transaction();
		ActivePhp\Base::$test_mode = false;
	}
	
}

?>