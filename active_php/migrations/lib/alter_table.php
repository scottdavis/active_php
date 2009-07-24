<?php
	class AlterTable {
		
		public function __construct($table_name, $options, $migration) {
				$this->passed_options = $options;
				$this->migration = $migration;
				$this->table_name = $table_name;
				$this->quoted_table_name = Migration::quote_table_name($table_name);
				$this->sql = 'ALTER TABLE ' . $this->quoted_table_name;
				$this->columns = array();
				$this->options = "ENGINE=InnoDB DEFAULT CHARSET=utf8";
				$this->other = array();
		}
		
		public function add_column($column_name, $type, $options = array()) {
			$defaults = array('limit' => NULL, 'precision' => NULL, 'scale' => NULL);
			$options = array_merge($defaults, $options);
			$add_column_sql = $this->alter_table_sql() . 'ADD COLUMN ' . Migration::quote_column_name($column_name) . ' ' . Migration::type_to_sql($type, $options['limit'], $options['precision'], $options['scale']);
			$add_column_sql = Migration::add_column_options($add_column_sql, $options);
			array_push($this->columns, $add_column_sql);
		}
		
		public function __call($method, $args) {
			$types = array_flip(array_keys(Migration::$NATIVE_DATABASE_TYPES));
			if(isset($types[$method])) {
				$args[1] = $method;
				call_user_func_array(array($this, 'add_column'), $args);
			}
		}
		
		
		public function index($column_name, $options = array()) {
			$columns = (is_array($column_name)) ? $column_name : array($column_name);
			$index_name = CreateTable::index_name($this->table_name, $columns);
			$quoted_columns = CreateTable::quote_column_names($columns);
			$index_type = (isset($options['unique']) && $options['unique']) ? ' UNIQUE' : '';
			array_push($this->columns, 'ALTER TABLE ' . $this->quoted_table_name . ' ADD INDEX ' . Migration::quote_column_name($index_name) . $index_type . ' (' . join(',', $quoted_columns) .')');
		}
		
		public function remove_index($column_name) {
			$columns = (is_array($column_name)) ? $column_name : array($column_name);
			$index_name = CreateTable::index_name($this->table_name, $columns);
			array_push($this->columns, $this->alter_table_sql() . 'DROP INDEX ' . $index_name);
		}
		public function foreign_key($column_name, $ref_table, $ref_column, $options = array('on_update' => 'CASCADE', 'on_delete' => 'CASCADE')) {
			$name = 'fk_' . $this->table_name . '_' . $column_name;
			$fk = 'CONSTRAINT ' . $name . 
						' FOREIGN KEY (' . Migration::quote_column_name($column_name) .
						') REFERENCES ' . Migration::quote_table_name($ref_table) . 
						' (' .  Migration::quote_column_name($ref_column) . ')' .
						' ON UPDATE ' . $options['on_update'] . ' ON DELETE ' . $options['on_delete'];
			array_push($this->columns, 'ALTER TABLE ' . $this->quoted_table_name . ' ADD ' . $fk);
		}
		
		public function remove($column) {
			//DROP COLUMN [column]
			array_push($this->columns, $this->alter_table_sql() . 'DROP COLUMN ' . Migration::quote_column_name($column));
		}
		
		public function column($name, $type, $options = array()) {
			$this->add_column($name, $type, $options);
		}
		
		public function change($column, $options = array()) {
			//MODIFY [column] column options
			if(isset($options['type']) && !empty($options['type'])) {
				$type = $options['type'];
			}else{
				$type = \ActivePhp\Base::select_one("SHOW COLUMNS FROM " . $this->quoted_table_name . " LIKE '{$column}'");
				$type = $type["Type"];
			}
			$sql = $this->alter_table_sql() . 'MODIFY COLUMN ' . Migration::quote_column_name($column) . ' ' . Migration::type_to_sql($type, $options['limit'], $options['precision'], $options['scale']);
			$sql = Migration::add_column_options($sql, $options, true);
			array_push($this->columns, $sql);
		}
		
		public function rename($old, $new) {
			//CHANGE [column] old new options
			$options = array();
			$current_type = \ActivePhp\Base::select_one("SHOW COLUMNS FROM " . $this->quoted_table_name . " LIKE '{$old}'");
			$current_type = $current_type["Type"];
			$rename_column_sql = $this->alter_table_sql() . 'CHANGE ' . Migration::quote_column_name($old) . ' ' . Migration::quote_column_name($new) . ' ' . $current_type;
			array_push($this->columns, $rename_column_sql);
		}
		
		public function remove_foreign_key($column) {
			//DROP FOREIGN KEY symbol
			$name = 'fk_' . $this->table_name . '_' . $column;
			array_push($this->columns, $this->alter_table_sql() . "DROP FOREIGN KEY $name");
		}
		
		public function remove_primary_key() {
			// DROP PRIMARY KEY
			array_push($this->columns, $this->alter_table_sql() . "DROP PRIMARY KEY");
		}
		
		private function alter_table_sql() {
			return "ALTER TABLE " . $this->quoted_table_name . ' ';
		}
		
		public function go($test = false) {
			foreach($this->columns as $sql) {
				if(!$test) {
					$this->migration->execute($sql . ";");
				}
			}
		}
		
		
	}
?>