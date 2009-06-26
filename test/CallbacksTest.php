<?php
require_once('PHPUnit/Framework.php');
require_once('TestSetup.php');


  /**
  * @package FrameworkTest
  */
  class CallbacksTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			ActivePhp\Base::$test_mode = true;
			User::start_transaction();
			User::$callback_tests = array();
		}
	
		public function testCreate() {
			$user = User::_create(array('name' => 'test'));
			$this->assertTrue(User::$callback_tests['before_create']);
			$this->assertTrue(User::$callback_tests['after_create']);
			$this->assertTrue(User::$callback_tests['before_validation']);
			$this->assertTrue(User::$callback_tests['after_validation']);
		}
		
		public function testUpdate() {
			$update = User::update(joe()->id, array('name' => 'joe_updated'));
			$this->assertTrue(User::$callback_tests['before_update']);
			$this->assertTrue(User::$callback_tests['after_update']);
			$this->assertTrue(User::$callback_tests['before_validation']);
			$this->assertTrue(User::$callback_tests['after_validation']);
		}
		
		public function tearDown() {
			User::end_transaction();
			ActivePhp\Base::$test_mode = false;
		}
		
		
	}
		
?>