<?php

require_once(dirname(__FILE__) . '/../../active_php/adapters/mysql_adapter.php');


	class MysqlAdapterTest extends PHPUnit_Framework_TestCase {
	
	
		public function setUp() {
			ActivePhp\Base::$adapter = new ActivePhp\MysqlAdapter(array('test_mode' => true));
			$this->adapter = ActivePhp\Base::$adapter;
			ActivePhp\Base::$class = 'Test';
		}
		
		public function testNewPrimaryKeyColumn() {
			$column = new ActivePhp\Column('id', 'primary_key');
			$this->assertEquals("`id` int(11) DEFAULT NULL auto_increment PRIMARY KEY", $column->to_sql());
			$this->assertTrue($column->isNumber());
			$this->assertTrue($column->isPrimaryKey());		
		}
		
		public function testNewTextColumnNotNull() {
			$column = new ActivePhp\Column('my_text', 'text', array('null' => false));
			$this->assertEquals("`my_text` text NOT NULL", $column->to_sql());
			$this->assertTrue($column->isText());
		}
		
		public function testNewDecimalColumn() {
			$column = new ActivePhp\Column('my_float', 'decimal', array('null' => false));
			$this->assertEquals("`my_float` decimal NOT NULL", $column->to_sql());
			$this->assertTrue($column->isNumber());
		}
		
		public function testNewFloatColumn() {
			$column = new ActivePhp\Column('my_float', 'float', array('null' => false));
			$this->assertEquals("`my_float` decimal NOT NULL", $column->to_sql());
			$this->assertTrue($column->isNumber());
		}
		
		public function testNewDecimalColumnWithPercision() {
			$column = new ActivePhp\Column('my_float', 'decimal', array('precision' => 5));
			$this->assertEquals("`my_float` decimal(5)", $column->to_sql());
			$this->assertTrue($column->isNumber());
		}
		
		public function testNewDecimalColumnWithPercisionAndScale() {
			$column = new ActivePhp\Column('my_float', 'decimal', array('precision' => 5, 'scale' => 2));
			$this->assertEquals("`my_float` decimal(5,2)", $column->to_sql());
		}
		
		/**
			* @expectedException Exception
			*/
		public function testNewDecimalColumnFails() {
			$column = new ActivePhp\Column('my_float', 'decimal', array('null' => false, 'scale' => '2'));
			$column->to_sql();
		}
		
		
		public function testConstructingFinderSql() {
			$options = array('from' => 'test', 'limit' => '0,1', 'joins' => 'WHOA DUDE', 'order' => 'test ASC', 'conditions' => "1=1 AND foo=bar");
			$this->assertEquals("SELECT * FROM `test` a, `WHOA` b, `DUDE` c WHERE(1=1 AND foo=bar) ORDER BY test ASC LIMIT 0,1",
			$this->adapter->construct_finder_sql($options));
		}
		public function testConstructingFinderSql2() {
			$options = array('select' => 'foo.a', 'from' => 'test', 'limit' => '0,1', 'joins' => 'WHOA DUDE', 'order' => 'test ASC', 'conditions' => "1=1 AND foo=bar");
			$this->assertEquals("SELECT foo.a FROM `test` a, `WHOA` b, `DUDE` c WHERE(1=1 AND foo=bar) ORDER BY test ASC LIMIT 0,1",
			$this->adapter->construct_finder_sql($options));
		}
		
		public function testQuoteTableName() {
			$this->assertEquals('`test`', $this->adapter->quote_table_name('test'));
		}
		
		public function testQuoteColumnName() {
			$this->assertEquals('`test`', $this->adapter->quote_column_name('test'));
		}
		

		
		
	}

?>