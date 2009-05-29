<?php

require_once('PHPUnit/Framework.php');
require_once('TestSetup.php');

  /**
  * @package FrameworkTest
	* @todo add validation tests
  */
  class CreateTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			ActivePhp\Base::$test_mode = true;
			User::start_transaction();
		}
		
		function testCreateNewUser() {
			$user = User::_create(array('name' => 'bob2'));
			$id = $user->id;
			$this->assertTrue(!empty($id));
			$this->assertTrue($user->saved);
		}
		
		function testCreateNewUserWithPhoto() {
			$user = User::_create(array('name' => 'bob2'));
			$id = $user->id;
			$this->assertTrue(!empty($id) && is_numeric($id));
			$this->assertTrue($user->saved);
			$photo = Photo::_create(array('user_id' => $id, 'title' => 'my photo'));
			$id = $photo->id;
			$this->assertTrue(!empty($id) && is_numeric($id));
			$this->assertTrue($photo->saved);
		}
		
		function testCreateNewUserWithPhotos() {
			$user = User::_create(array('name' => 'bob2'));
			$id = $user->id;
			$this->assertTrue(!empty($id) && is_numeric($id));
			$this->assertTrue($user->saved);
			foreach(range(0,100) as $i) {
				$photo = Photo::_create(array('user_id' => $id, 'title' => 'my photo ' . $i));
				$id2 = $photo->id;
				$this->assertTrue(!empty($id2) && is_numeric($id2));
				$this->assertTrue($photo->saved);
			}
		}
		
		function tearDown() {
			User::end_transaction();
			ActivePhp\Base::$test_mode = false;
		}
		
		
	}
?>