<?php

require_once(dirname(__FILE__) . '/../../active_php/adapters/sqlite3_adapter.php');


	class Sqlite3AdapterTest extends PHPUnit_Framework_TestCase {
		
		public function setUp() {
			$this->db = dirname(__FILE__) . '/test.db';
			$this->adapter = new ActivePhp\Sqlite3Adapter(array('file' => $this->db));
			ActivePhp\Base::$class = 'Test';
			$this->adapter->query('CREATE TABLE IF NOT EXISTS foo (id INTEGER PRIMARY KEY, bar STRING)');
		}
		
		public function testTruth() {
			$this->adapter->query("INSERT INTO foo (bar) VALUES ('This is a test1')");
			$this->adapter->query("INSERT INTO foo (bar) VALUES ('This is a test2')");
			$this->adapter->query("INSERT INTO foo (bar) VALUES ('This is a test3')");
			$result = $this->adapter->query('SELECT * FROM foo');
			$this->assertTrue(is_a($result, 'ActivePhp\Sqlite3Result'));
			while($row = $result->fetch_assoc()) {
				$this->assertTrue(is_array($row));
			}
			$this->assertEquals(3, $result->num_rows());
			$result->free();
		}
		
		
		public function tearDown() {
			$this->adapter->query('DELETE FROM foo');
		}
		
	}
	
?>