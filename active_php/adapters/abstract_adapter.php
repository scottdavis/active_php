<?php

namespace ActivePhp;
require_once(dirname(__FILE__) . '/../base.php');
require_once(dirname(__FILE__) . '/abstract/column.php');
require_once(dirname(__FILE__) . '/abstract/database_logger.php');
require_once(dirname(__FILE__) . '/abstract/query_cache.php');
require_once(dirname(__FILE__) . '/abstract/table.php');
require_once(dirname(__FILE__) . '/mysql_adapter.php');
abstract class AbstractAdapter {
	
		protected $open_transactions = 0;
		protected $logger = NULL;
		
		public function __construct($options) {
			$this->logger = new DatabaseLogger();
			$this->connect($options);
			Base::set_connection($this->connection);
		}
		
		public function __destruct() {
			$this->close();
		}
		
		public function set_connection_to_base($conn) {
			
		}
		
		public function connect() {return false;}
		
		public function close() {}
		
		public function reset() {}
		
		public function insert_id() {}
		
		public function adapter_name() {return 'Abstract';}
		
		public function supports_migrations() {return false;}
		
		public function supports_count_distinct() {return true;}
		
		public function supports_ddl_transactions() {return false;}
		
		public function supports_savepoints() {return false;}
		
		public function prefetch_primary_key($table_name = NULL) {return false;}
		
		public function quote_table_name($name) { return $this->quote_column_name($name);}
		
		public function quote_column_name($name) {return $name;} 
		
		public function raw_connection() { return $this->connection; }
		
		
		public function open_transactions() {
			return $this->open_transactions;
		}
		
		public function increment_open_transactions() {
			return $this->open_transactions++;
		}
		
		public function decrement_open_transactions() {
			return $this->open_transactions--;
		}
		
		public function current_savepoint() {
			return 'savepoint_' . $this->open_transactions();
		}
		
		public function create_savepoint() {}
		
		public function rollback_to_savepoint() {}
		
		public function log_query($name, $sql) {
			$this->logger->add_message(sprintf("[%s] %s ", $name, $sql));
		}
		
		public function type_to_sql() {}
		
		public function add_column_options() {}
		
		public function build_conditions() {}
		
		public function add_joins($joins) {
			$values = range('b', 'z');
			if(is_string($joins)) {
				$joins = explode(" ", $joins);
			}
			$out = array();
			$i = 0;
			foreach($joins as $join) {
				$quoted = $this->quote_table_name($join);
				$variable = $values[$i];
				$out[] = "$quoted $variable";
				$i++;
			}
			return ', '. join($out, ', ');
		}
		
		
		public function construct_finder_sql($options) {
			$defaults = array('select' => '*', 'joins' => '');
			$options = array_merge($defaults, $options);
			$sql = "SELECT ";
			$sql .= trim($options['select']) . ' ';
			$from = $this->quote_table_name($options['from']);
			$sql .= "FROM $from";
			if(!empty($options['joins'])) {
				$sql .= ' a';
				$sql .= $this->add_joins($options['joins']);
			}
			!isset($options['conditions']) ?: $sql .= ' WHERE(' . $options['conditions'] . ')';
			!isset($options['order']) ?: $sql .= ' ORDER BY ' . $options['order'];
			!isset($options['limit']) ?: $sql .= ' LIMIT ' . $options['limit'];
			return $sql;
		}
		
		
	}
	
	
abstract class QueryResult {
		var $failed = false;
		public function __construct($object) {
			if($object === false) {
				$this->failed = true;
			}
			$this->query = $object;
		}
		
		public function hasFailed() {return $this->failed;}
		
		public function fetch_assoc() {}
		
		public function free() {}
		
	}

?>