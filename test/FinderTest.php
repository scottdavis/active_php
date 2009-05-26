<?php

require_once('PHPUnit/Framework.php');
require_once('TestSetup.php');
  /**
  * @package FrameworkTest
  */
  class FinderTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			ActivePhp\Base::$test_mode = true;
			$this->users = array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie');
			$this->user_ids = collect(function($o){return $o->id;}, User::find_all());
		}
		
		public function testFindAll() {
			$users = User::find_all();
			$users = collect(function($o){return $o->name;}, $users);
			//we sort them to assure they are compaired in an order that will return true
			$this->assertEquals(asort($users), asort($this->users));
		}
		
		
		public function testFind() {
			//test finding by ids without cache
			foreach($this->user_ids as $id) {
				$_user = User::_find($id);
				$this->assertTrue((isset($_user) && !empty($_user)));
			}
			//test finding by ids with cache
			foreach($this->user_ids as $id) {
				$_user = User::find($id);
				$this->assertTrue((isset($_user) && !empty($_user)));
			}
		}
		
		public function testMassFindByIds() {
			$results = call_user_func_array(array('User', 'find'), $this->user_ids);
			$this->assertEquals($results->length, count($this->user_ids));
		}
		
		public function testFindBy() {
			//this makes sure finder is returning the vaid data
			foreach($this->users as $user) {
				$_user = User::find_by(array('conditions' => "name = '" . $user . "'"));
				$lamda = call_user_func(strtolower($user));
				//lots of redudent checking here to make sure the lamda function is also returning the right data
				$this->assertEquals($lamda->name, $_user->name);
				$this->assertEquals($user, $lamda->name);
				$this->assertEquals($user, $_user->name);
				$this->assertEquals($lamda, $_user);
			}
		}
		
		
		
		
		/**
		* @expectedException ActivePhp\RecordNotFound
		*/
		public function testFindFails() {
			User::_find(500);
		}
		
		
		public function tearDown() {
			ActivePhp\Base::$test_mode = false;
		}
	
	}


?>