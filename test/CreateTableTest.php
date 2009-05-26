<?php

require_once('PHPUnit/Framework.php');
require_once('TestSetup.php');
  /**
  * @package FrameworkTest
  */
  class CreateTableTest extends PHPUnit_Framework_TestCase {
	
		public function setUp() {
			$this->m = new Migration;
			$this->m->drop_table('users');
			$this->m->drop_table('photos');
		}
		
		public function testCreateUsersTable() {
			$g = new TestMigration();
			$g->up();
			$this->assertEquals('', mysql_error());
			$result = mysql_query('show tables from ' . MYSQL_DATABASE);
			$rows = mysql_num_rows($result);
			$this->assertEquals(2, $rows);
		}
		
		public function tearDown() {
			load_test_data();
		}
	
	}

?>