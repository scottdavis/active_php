<?php 

require_once('PHPUnit/Framework.php');
require_once('TestSetup.php');
  /**
  * @package FrameworkTest
	* @todo add conditions checking
  */
  class MathTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			ActivePhp\Base::$test_mode = true;
		}
		
		public function testMax() {
		  $this->assertEquals(9, User::max(array('column' => 'id')));
		}
		
		public function testMin() {
		  $this->assertEquals(1, User::min(array('column' => 'id')));
		}
		
		public function testCount() {
		  $this->assertEquals(9, User::count());
		}
		public function testSum() {
		$this->assertEquals(45, User::sum(array('column' => 'id')));
		}
		
		public function tearDown() {
		  ActivePhp\Base::$test_mode = false;
		}
	}

?>
