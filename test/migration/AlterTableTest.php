<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__) . '/../config/db_connect.php');
require_once(dirname(__FILE__) . '/../../active_php/migrations/lib/alter_table.php');
require_once(dirname(__FILE__) . '/../../active_php/migrations/migration.php');
  /**
  * @package FrameworkTest
  */
  class AlterTableTest extends PHPUnit_Framework_TestCase {
		
		public function setUp() {
			$this->table = new AlterTable('users', array(), new Migration());
		}
		
		public function testAssignsTable() {
			$this->assertTrue(isset($this->table->quoted_table_name));
			$this->assertTrue(isset($this->table->table_name));
			$this->assertEquals('users', $this->table->table_name);
			$this->assertEquals('`users`', $this->table->quoted_table_name);
		}
		
		
		public function testRenameColumn() {
			$this->table->rename('name', 'name2');
			$this->assertFalse(empty($this->table->columns[0]));
			$this->assertEquals("ALTER TABLE `users` CHANGE `name` `name2` varchar(255)", $this->table->columns[0]);
		}
		
		public function testChangeColumn() {
			$this->table->change_column('name', 'integer', array('null' => false));
			$this->assertEquals("ALTER TABLE `users` MODIFY COLUMN `name` int(11) NOT NULL", $this->table->columns[0]);
		}
		
		
		public function testDropColumn() {
			$this->table->remove('name');
			$this->assertEquals("ALTER TABLE `users` DROP COLUMN `name`", $this->table->columns[0]);
		}
		
		public function testDropIndex() {
			$this->table->remove_index('name');
			$this->assertEquals("ALTER TABLE `users` DROP INDEX index_users_on_name", $this->table->columns[0]);
		}
		
		public function testDropPrimaryKey() {
			$this->table->remove_primary_key(); 
			$this->assertEquals("ALTER TABLE `users` DROP PRIMARY KEY", $this->table->columns[0]);
		}
		
		public function testRemoveForeignKey() {
			$this->table = new AlterTable('photos', array(), new Migration());
			$this->table->remove_foreign_key('user_id');
			$this->assertEquals("ALTER TABLE `photos` DROP FOREIGN KEY fk_photos_user_id", $this->table->columns[0]);
		}
		
		public function testAddforeignKey() {
			$this->table = new AlterTable('photos', array(), new Migration());
			$this->table->foreign_key('user_id', 'users', 'id');
			$this->assertEquals("ALTER TABLE `photos` ADD CONSTRAINT fk_photos_user_id FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE CASCADE", $this->table->columns[0]);
		}
		
		public function testAddIndex() {
			$this->table->index('name');
			$this->assertEquals("ALTER TABLE `users` ADD INDEX `index_users_on_name` (`name`)", $this->table->columns[0]);
		}
		
		public function testIndexTwoCols() {
			$this->table->index(array('name', 'my_int'));
			$this->assertEquals("ALTER TABLE `users` ADD INDEX `index_users_on_name_and_my_int` (`name`,`my_int`)", $this->table->columns[0]);
		}
		
		public function testAddIndexUnique() {
			$this->table->index('name', array('unique' => true));
			$this->assertEquals("ALTER TABLE `users` ADD INDEX `index_users_on_name` UNIQUE (`name`)", $this->table->columns[0]);
		}
		
		public function testAddString() {
			$this->table->string('name');
			$this->assertEquals("ALTER TABLE `users` ADD COLUMN `name` varchar(255)", $this->table->columns[0]);
		}
		
		public function testAllMagicTypes() {
			$types = array_keys(Migration::$NATIVE_DATABASE_TYPES);
			$i = 0;
			foreach($types as $type) {
				$this->table->$type('name' . $i);
				$i++;
			}
			$this->assertEquals(count($types), count($this->table->columns));
		}
		
	
	}