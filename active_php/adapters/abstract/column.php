<?php

namespace ActivePhp;

	class Column {
		
		var $name;
		var $sql_type;
		var $sql_types;
		var $options;
		var $limit;
		var $precision;
		var $scale;
		var $native_types;
		
		public function __construct($name, $sql_type, $options = array()) {
			$this->adapter = Base::adapter();
			$adapter_name = 'ActivePhp\\' . $this->adapter->adapter_name() . 'Adapter';
			$this->name = $name;
			$this->sql_type = $sql_type;
			$this->options = $options;
			
			foreach(array('limit', 'precision', 'scale') as $arg) {
				if(isset($this->options[$arg])) {
					$this->$arg = $this->options[$arg];
				}
			}
			$this->native_types = $adapter_name::$NATIVE_DATABASE_TYPES;
			$this->sql_types = array_keys($this->native_types);
			if(!isset($this->native_types[$this->sql_type])) {
				throw new \Exception('Type does not exist');
			}
			
		}
		
		public function isText() {
			return $this->sql_type == 'string' || $this->sql_type == 'text';
		}
		
		public function isNumber() {
			return $this->sql_type == 'integer' || $this->sql_type == 'float' || $this->sql_type == 'decimal' || $this->sql_type = 'primary_key';
		}
		
		public function isPrimaryKey() {
			return $this->sql_type == 'primary_key';
		}
		
		public function isDate() {
			return $this->sql_type == 'timestamp' || $this->sql_type == 'datetime' || $this->sql_type == 'date';
		}
		
		public function to_sql() {
			$add_column_sql = $this->adapter->quote_column_name($this->name) . ' ' .
			$this->adapter->type_to_sql($this->sql_type, $this->limit, $this->precision, $this->scale);
			$add_column_sql = $this->adapter->add_column_options($add_column_sql, $this->options);
			return $add_column_sql;
		}
		
		
	}

?>