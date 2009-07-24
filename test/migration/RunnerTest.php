<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../config/db_connect.php');
require_once(dirname(__FILE__) . '/../../active_php/migrations/lib/migration_runner.php');
require_once(dirname(__FILE__) . '/../../active_php/migrations/migration.php');



/**
* @package FrameworkTest
*/
class RunnerTest extends PHPUnit_Framework_TestCase {
	
	public function setUp() {
		MigrationRunner::$dir = dirname(__FILE__) . '/test';
	}
	
	public function testMigrationTable() {
		$this->assertEquals('migrations', MigrationRunner::migration_table_name());
	}
	
	
	public function testMigrateUpTo() {
		MigrationRunner::up(123456788);
		$this->assertEquals(123456788, MigrationRunner::current_version());
	}
	
	
	public function testLoadFiles() {
		$data = MigrationRunner::load_files();
		$last = 0;
		foreach($data as $version => $class) {
			$this->assertTrue(is_numeric($version));
			//assert sorted
			$this->assertTrue($last < $version);
			$klass = new $class();
			$this->assertTrue(isset($klass));
			$last = $version;
		}
	}
	
	public function testUp() {
		MigrationRunner::up();
		$this->assertEquals(MigrationRunner::current_version(), 123456789);
	}
	
	
	public function testUpThenDown() {
		MigrationRunner::up(123456789);
		$this->assertEquals(MigrationRunner::current_version(), 123456789);
		MigrationRunner::down(123456788);
		$this->assertEquals(MigrationRunner::current_version(), 123456788);
	}
	
	public function testGetMaxVersion() {
		MigrationRunner::up(123456789);
		$data = MigrationRunner::load_files();
		$max = MigrationRunner::current_version();
		$this->assertEquals(123456789, $max);
	}
	
	
}

?>