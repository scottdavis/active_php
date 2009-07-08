<?php

namespace ActivePhp;

require_once(dirname(__FILE__) . '/abstract_adapter.php');

	class MysqlAdapter extends AbstractAdapter {
		
		public function connect($db_settings_name) {
			$this->options = $db_settings_name;
			$this->connection = mysql_connect($db_settings_name['host'], $db_settings_name['username'], $db_settings_name['password']) or die();
			mysql_select_db($this->options['database'], $this->connection);
			return $this->connection;
		}
		
		public function close() {
			if(!empty($this->connection)) {
				mysql_close($this->connection);
			}
		}
		
		public function reset() {
			$this->close();
			$conn = $this->connect($this->options);
			Base::set_connection($conn);
		}
		
		public function query($sql) {
			$result = mysql_query($sql, $this->connection);
			if($result === false) {
				throw new \Exception('Query Failed: ' . mysql_error($this->connection));
			}
			return new MysqlQueryResult($result);
		}
		
		public function insert_id() {
			return mysql_insert_id($this->connection);
		}
		
		
		public static $NATIVE_DATABASE_TYPES = array(
        	'primary_key' => array('name' => NULL, 'sql' => "int(11) DEFAULT NULL auto_increment PRIMARY KEY"),
        	'string'      => array('name' => "varchar", 'limit' => 255),
        	'text'        => array('name' => "text"),
        	'integer'     => array('name' => "int", 'limit' => 11),
        	'float'       => array('name' => "decimal"),
        	'decimal'     => array('name' => "decimal"),
       	 	'datetime'    => array('name' => "datetime"),
        	'timestamp'   => array('name' => "datetime"),
        	'time'        => array('name' => "time"),
        	'date'        => array('name' => "date"),
        	'binary'      => array('name' => "blob"),
        	'boolean'     => array('name' => "tinyint", 'limit' => 1 )
			);
		
		public function adapter_name() {return 'Mysql';}
		
		public function quote_column_name($name) {
        	return '`' . $name . '`';
      	}
 
 		public function quote_table_name($name) {
        return preg_replace('/\./', '`.`', self::quote_column_name($name));
		}
		
		public function type_to_sql($type, $limit = NULL, $precision = NULL, $scale = NULL) {
			if(in_array($type, array_keys(static::$NATIVE_DATABASE_TYPES))) {
			(string) $column_type_sql = static::$NATIVE_DATABASE_TYPES[$type]['name'];
				switch($type) {
					case 'decimal':
						if(!empty($precision)) {
							if(!empty($scale)) {
								$column_type_sql .= '(' . $precision . ',' . $scale . ')';
							}else{
								$column_type_sql .= '(' . $precision . ')';
							}
						}else{
							if(!empty($scale) && empty($precision)) {
								throw new \Exception("Error adding decimal column: precision cannot be empty if scale if specified");
							}
						}
					break;
					case 'primary_key':
						return $column_type_sql = static::$NATIVE_DATABASE_TYPES[$type]['sql'];
					break;
				}
		
				if(isset($limit) || isset(static::$NATIVE_DATABASE_TYPES[$type]['limit'])) {
					$limit = isset($limit) ? $limit : static::$NATIVE_DATABASE_TYPES[$type]['limit'];
					$column_type_sql .= '(' . $limit . ')';
				}
				return $column_type_sql;
			}else{
				return $type;
			}
		}
		
		public function add_column_options($sql, $options = array()) {
			$sql .= isset($options['default']) ? ' DEFAULT ' . $options['default'] : '';
			$sql .= (isset($options['null']) && !$options['null']) ? ' NOT NULL' : '';
			return $sql;
		}
		
	}
	
	
	class MysqlQueryResult extends QueryResult {
		
		public function fetch_assoc() {
			return mysql_fetch_assoc($this->query);
		}
		
		public function num_rows() {
			return mysql_num_rows($this->query);
		}
		
		public function free() {
			if(!$this->hasFailed()) {
				mysql_free_result($this->query);
			}
		}
		
	}
	

?>