<?php
	require_once(dirname(__FILE__) . '/lib/create_table.php');
	require_once(dirname(__FILE__) . '/../base.php');
	class Migration {
	
		public function create_database($database_name, $charset = 'utf8') {
			return $this->execute("CREATE DATABASE `" . $database_name . "` DEFAULT CHARACTER SET `" . $charset . "`");
		}
		
		
		public function drop_database($database_name) {
			return $this->execute("DROP DATABASE IF EXISTS `" . $database_name . "`");
		}
		
		
		public function create_table($table_name, $options = array()) {
			return new CreateTable($table_name, $options, $this);
		}
		
		public function drop_table($table_name) {
			return $this->execute('DROP TABLE ' . self::quote_table_name($table_name));
		}
		
		public function remove_column($table_name, $column) {
			
		}
		
		public function rename_column($table_name, $column_name, $new_column_name) {
			$column_data = ActivePhp\Base::select_one("SHOW COLUMNS FROM " . self::quote_table_name($table_name) . " LIKE '" . $column_name . "'", false);
			$current_type = $column_data['Type'];
			$rename_column_sql = "ALTER TABLE " . self::quote_table_name($table_name) . " CHANGE " . self::quote_column_name($column_name) ." " . self::quote_column_name($new_column_name) . ' ' . $current_type;
			$this->execute($rename_column_sql);
		}
	
		
		public function run($down = false) {
			if($down) {
				$this->down();
			}else{
				$this->up();
			}
		}
		
		
		public function execute($sql) {
			var_dump($sql);
			$query = ActivePhp\Base::execute($sql);
			return $query;
		}
		
		
		
		//static vars
		
		public static $NATIVE_DATABASE_TYPES = array(
        	'primary_key' => array('name' => NULL, 'sql' => "int(11) DEFAULT NULL auto_increment PRIMARY KEY"),
        	'string'      => array('name' => "varchar", 'limit' => 255),
        	'text'        => array('name' => "text"),
        	'integer'     => array('name' => "int", 'limit' => 11),
        	'float'       => array('name' => "float"),
        	'decimal'     => array('name' => "decimal"),
       	 	'datetime'    => array('name' => "datetime"),
        	'timestamp'   => array('name' => "datetime"),
        	'time'        => array('name' => "time"),
        	'date'        => array('name' => "date"),
        	'binary'      => array('name' => "blob"),
        	'boolean'     => array('name' => "tinyint", 'limit' => 1 )
			);
		
		
		
		
		
		//static methods
		public static function type_to_sql($type, $limit = NULL, $precision = NULL, $scale = NULL) {
			if(in_array($type, array_keys(self::$NATIVE_DATABASE_TYPES))) {
			(string) $column_type_sql = self::$NATIVE_DATABASE_TYPES[$type]['name'];
				switch($type) {
					case 'decimal':
						if(!empty($precision)) {
							if(!empty($scale)) {
								$columns_type_sql .= '(' . $precision . ',' . $scale . ')';
							}else{
								$columns_type_sql .= '(' . $precision . ')';
							}
						}else{
							//todo throw error : "Error adding decimal column: precision cannot be empty if scale if specified"
						}
					break;
					case 'primary_key':
						return $column_type_sql = self::$NATIVE_DATABASE_TYPES[$type]['sql'];
					break;
				}
		
				if(isset($limit) || isset(self::$NATIVE_DATABASE_TYPES[$type]['limit'])) {
					$limit = isset($limit) ? $limit : self::$NATIVE_DATABASE_TYPES[$type]['limit'];
					$column_type_sql .= '(' . $limit . ')';
				}
				
				return $column_type_sql;
			}else{
				return $type;
			}
		}
		
		public static function add_column_options($sql, $options = array()) {
			$sql .= isset($options['default']) ? ' DEFAULT ' . $options['default'] : '';
			$sql .= (isset($options['null']) && !$options['null']) ? ' NOT NULL' : '';
			return $sql;
		}
		
		
		public static function quote_column_name($name) {
        	return '`' . $name . '`';
      	}
 
 		public static function quote_table_name($name) {
        	return preg_replace('/\./', '`.`', self::quote_column_name($name));
		}
		
		
		
		
	}

?>