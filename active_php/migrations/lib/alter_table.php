<?php
	class AlterTable {
		
		public function __construct($table_name, $options, $migration) {
				public function __construct($table_name, $options, $migration) {
					$this->passed_options = $options;
					$this->migration = $migration;
					$this->table_name = $table_name;
					$this->quoted_table_name = Migration::quote_table_name($table_name);
					$this->sql = 'ALTER TABLE ' . $this->quoted_table_name;
					$this->columns = array();
					$this->options = "ENGINE=InnoDB DEFAULT CHARSET=utf8";
					$this->other = array();

					if(isset($this->passed_options['id']) && !$this->passed_options['id']) {
						//do nothing
					}else{
						array_push($this->columns, "id int(11) DEFAULT NULL auto_increment PRIMARY KEY");
					}
				}
		}
		
		
		public function change_column() {
			
		}
		
		
		public function rename_column($column_name, $new_column_name) {
				$options = array();
				$column = Migration::columns_data($this->table_name, $column_name);
			}
		
	}
?>