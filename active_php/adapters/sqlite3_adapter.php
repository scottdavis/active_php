<?php

namespace ActivePhp;

require_once(dirname(__FILE__) . '/abstract_adapter.php');

	class Sqlite3Adapter extends AbstractAdapter {
		
		public function connect($options) {
			if(empty($options)) {throw new \Exception("You mus supply options to create a database");}
			$this->connection = new \SQLite3($options['file']);
		}
		
		private static function is_exec($sql) {
			foreach(array('insert', 'update', 'delete') as $t) {
				if(preg_match("/^$t/", strtolower($sql))) {
					return true;
				}
			}
			return false;
		}
		
		public function query($sql) {
			if(!$this->is_exec($sql)){
				$result = $this->connection->query($sql);
			}else{
				$result = $this->connection->exec($sql);
			}
			return new Sqlite3Result($result);
		}
		
		public function insert_id() {
			return $this->connection->lastInsertRowID();
		}
		
	}
	
	
	class Sqlite3Result extends QueryResult {
		
		public function __construct($result) {
			if(is_bool($result)){
				$data = $result;
			}else{
				$data = array();
				$this->result = $result;
				while($row = $result->fetchArray(SQLITE3_ASSOC)) {
					$data[] = $row;
				}
				reset($data);
			}
			parent::__construct($data);
		}
		
		
		private function get_value() {
			$result = current($this->query);
			next($this->query);
			return $result;
		}
		
		public function fetch_assoc() {
			return is_bool($this->query) ? $this->query : $this->get_value();
		}
		
		public function num_rows() {
			return count($this->query);
		}
		
		public function free() {
			unset($this->query);
			$this->result->finalize();
		}
		
	}
	
?>