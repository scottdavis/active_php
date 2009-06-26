<?php
/**
* TODO
* Add prodected setter support eg. define an array of elements that are not allowed to be set by mass assignment
* Add readonly support make a column readonly
* Add create, update, and other methods that active record has
* Test with phpunit
*/


namespace ActivePhp;

	require_once(dirname(__FILE__) . '/../active_support/base.php');
	require_once(dirname(__FILE__) . '/exceptions.php');
	require_once(dirname(__FILE__) . '/result_collection.php');
	require_once(dirname(__FILE__) . '/migrations/migration.php');
	require_once(dirname(__FILE__) . '/errors.php');
	require_once(dirname(__FILE__) . '/validate.php');
	
class Base {
	
	/** public vars */
	public static $query_log = array();

	/** protected vars */
	protected static $database;
	protected static $connection;
	protected static $table;
	protected static $primary_key_field = 'id';
	protected static $associations = array();
	protected static $query_cache = array();
	protected static $columns = array();
	protected static $validations = array();
	protected static $my_class;
	protected static $table_names = array();
	public static $test_mode = false;
	protected static $temp = array();
	
	
	
	var $update_mode = false;
	var $saved;
	var $errors;
	
	/**
	* Required
	* connects to the data base and stores the connection
	* @param $db_settings_name Array
	*/
	public static function establish_connection($db_settings_name) {
	static::$connection = mysql_connect($db_settings_name['host'], $db_settings_name['username'], $db_settings_name['password']);
	static::$database = $db_settings_name['database'];
	mysql_select_db(static::$database, static::connection());
	}
    /**
	* returns the quoted table name
	*/
	protected static function table_name() {
		if(isset(static::$table_names[static::$class]) && !empty(static::$table_names[static::$class])) { 
			$name = static::$table_names[static::$class];
		}else{
			static::$table_names[static::$class] = strtolower(\Inflector::pluralize(static::$class));
			$name = static::$table_names[static::$class];
		}
		return "`{$name}`";
	}
	
	
	public static function test_mode() {
		return static::$test_mode;
	}
	
	/**
	* returns unquoted table name
	*/
	public static function real_table_name() {
		return static::$table_name;
	}
	/**
	* returns name of the primary key field
	*/
	protected static function primary_key_field() {
		return static::$primary_key_field;
	}

	protected static function associations() {
		return static::$associations;
	}

	protected static function connection() {
		return static::$connection;
	}

	public static function database() {
		return static::$database;
	}
	
	public function class_name() {
		return static::$class;
	}
	
	public static function getClass() {
		if(!isset(static::$my_class) && empty(static::$my_class)) {
			static::$my_class = new static::$class;
		}
		return static::$my_class;
	}
	

	private static function sanatize_input_array($input) {
		if(is_array($input)) {
		$clean_values = array();
		foreach($input as $value) {
			array_push($clean_values, mysql_real_escape_string($value));
		}
		return $clean_values;
		}else{
		//if its an int make it a string
			return mysql_real_escape_string((string) $input);
		}
	}
	
	
	/**
	* MATH METHODS
	*/
	
	private static function isInteger($input){
		return is_numeric($input);
	}
	
	
	/**
	* @see self::check_args_for_math_functions($options)
	* @param $options array('column' => 'name', 'conditions' => array('id' => 1))  
	*/
	protected static function check_args_for_math_functions($options){
		//verify options contains a column value
		if(!is_array($options) || !isset($options['column'])){
			throw new \ActivePhp\ActivePhpException('InvalidArguments - please include a column ex. array(\'column\' => \'id\')');
		}
	}
	/**
	* Method count
	* use Class::count(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function count($options = array('column' => '*', 'conditions' => NULL)) {
		self::check_args_for_math_functions($options);
		$sql = 'SELECT count(' . $options['column'] . ') AS count_all FROM ' . self::table_name();
		$sql .= isset($options['conditions']) ? self::build_conditions($options['conditions']) : '';
		$sql .= ';';
		return self::execute_query($sql, false)->count_all;
	}
	/**
	* Method sum
	* use Class::sum(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function sum($options = array('column' => NULL, 'conditions' => NULL)) {
		self::check_args_for_math_functions($options);
		$sql = 'SELECT sum('. self::table_name() . '.' . $options['column'] . ') as sum_all FROM ' . self::table_name();
		$sql .= isset($options['conditions']) ? self::build_conditions($options['conditions']) : '';
		return self::execute_query($sql, false)->sum_all;
	}
	/**
	* Method max
	* @uses Class::max(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function max($options = array('column' => NULL, 'conditions' => NULL)) {
		self::check_args_for_math_functions($options);
		$sql = 'SELECT max('. self::table_name() . '.' . $options['column'] . ') as max_all FROM ' . self::table_name();
		$sql .= isset($options['conditions']) ? self::build_conditions($options['conditions']) : '';
		return self::execute_query($sql, false)->max_all;
	}
	/**
	* Method min
	* @uses Class::min(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function min($options = array('column' => NULL, 'conditions' => NULL)) {
		self::check_args_for_math_functions($options);
		$sql = 'SELECT min('. self::table_name() . '.' . $options['column'] . ') as min_all FROM ' . self::table_name();
		$sql .= isset($options['conditions']) ? self::build_conditions($options['conditions']) : '';
		return self::execute_query($sql, false)->min_all;
	}
	/**
	* Method build_conditions
	* use self::build_conditions(array('name' => 'bob')) or self::build_conditions('id = 3')
	* @param $conditions Array || String
	*/
	private static function build_conditions($conditions) {
			$sql = '';
			if(is_array($conditions)){
				$sql .= ' ' . self::build_where_from_array($conditions);
			}else if(is_string($conditions)){
				$sql .= ' WHERE(' . $conditions . ')';
			}
			return $sql;
	}
	
	
	/**
	* END MATH METHODS
	*/
	
	/**
	*	START FIND METHODS
	*/
	
	/**
	* Method find
	* use self::find(1,2,3,4,5) or self::find(3) or self::find('3')
	* @param string|integer|array $args
	*/
	public static function find() {
		$array_or_id = func_get_args();
		$return = self::build_find_sql($array_or_id);
		$sql = $return[0];
		$all = $return[1];
		return self::execute_query($sql, $all);
	}
		
	
	public static function _find() {
		$array_or_id = func_get_args();
		$return = self::build_find_sql($array_or_id);
		$sql = $return[0];
		$all = $return[1];
		return self::execute_query($sql, $all, false);
	
	}
  
  public static function delete($id) {
    $clean = self::sanatize_input_array($id);
    if(is_array($id)) {
      $where = ' WHERE (id IN (' . join(',', $clean) . '))';
    }else{
      $where = ' WHERE (id = ' . $clean . ')';
    }
    $sql = 'DELETE FROM ' . self::table_name() . $where;
    return self::execute_insert_query($sql);
  }
  
  public function destroy() {
    self::delete($this->id);
  }
	
	public static function delete_all() {
		$sql = 'DELETE FROM ' . self::table_name() . ';';
		return self::execute_insert_query($sql);
	}
	
	public static function truncate() {
		$sql = 'TRUNCATE ' . self::table_name() . ';';
		return self::execute_insert_query($sql);
	}
	
	
	private static function build_find_sql($array_or_id) {
		$all = false;
		$clean = self::sanatize_input_array($array_or_id);
		if($array_or_id[0] == 'first') {
			$limit = '0,1';
		}
		elseif(count($clean) == 1){
			$where = "id = " . $clean[0];
		}
		elseif(is_array($clean)) {
			$all = true;
			$where = "id IN (" . join(',', $clean) . ")";
		}
		$sql = "SELECT * FROM " . self::table_name();
		if(isset($where)) {
			$sql .= ' WHERE (' . $where . ')';
		}
		if(isset($limit)){
			$sql .= ' LIMIT ' . $limit;
		}
		$sql .=';';
		return array($sql, $all);
	}

	
  	/**
	* Method find_all
	* use self::find_all(array('condtions' => 'name = bob')) or self::find_all()
	* @param options Array
	*/
	public static function find_all($options = array()) {
		$sql = 'SELECT * FROM ' . self::table_name() .  
		(isset($options['conditions'])  ? ' WHERE ' . $options['conditions']  : '') . 
		(isset($options['order'])       ? ' ORDER BY ' . $options['order']      : '') . 
		(isset($options['limit'])       ? ' LIMIT ' . $options['limit']       : '') . ';';
		
		return self::execute_query($sql, true);
	}
	
	/**
	* Method find_by
	* use self::find_by(array('condtions' => 'name = bob')) or self::find_all()
	* @param options Array
	*/
	public static function find_by($options = array()) {
		$sql = 'SELECT * FROM ' . self::table_name() .  
		(isset($options['conditions'])  ? ' WHERE ' . $options['conditions']  : '') . 
		(isset($options['order'])       ? ' ORDER BY ' . $options['order']      : '') .
		(isset($options['limit'])       ? ' LIMIT ' . $options['limit']       : '') .  ';';
		return self::execute_query($sql, false);
	}
	
	/**
	* Method _find_by
	* find_by without cache
	* use self::find_by(array('condtions' => 'name = bob')) or self::find_all()
	* @param options Array
	*/
	public static function _find_by($options = array()) {
		$sql = 'SELECT * FROM ' . self::table_name() .  
		(isset($options['conditions'])  ? ' WHERE ' . $options['conditions']  : '') . 
		(isset($options['order'])       ? ' ORDER BY ' . $options['order']      : '') .
		(isset($options['limit'])       ? ' LIMIT ' . $options['limit']       : '') .  ';';
		return self::execute_query($sql, false, false);
	}
	
	/**
	* END FIND METHODS
	*/
	
	/**
	* START VALIDATION CHECKS
	*/
	
	
	/** Validation Callbacks */
	
	public function before_validation() {}
	public function after_validation() {}
	
	public static function run_validations($klass) {	
		foreach(get_class_methods(static::$class) as $method) {
			$matches = array();
			if(preg_match('/^validators_for_([0-9a-z_]+)/', $method, $matches)) {
			$columns = explode('_and_', $matches[1]);
			call_user_func_array(array($klass, $method), array($columns));
			}
		}
	}
	
	public static function getErrors($klass) {
		call_user_func_array(array($klass, 'before_validation'), array());
		/** Run default mysql validations based on column types */
		self::column_validatons($klass);
		/** run customn user made validations */
		self::run_validations($klass);
		call_user_func_array(array($klass, 'after_validation'), array());
	}
	
	
	public function isValid() {
		static::getErrors($this);
		return count($this->errors) == 0 ? true : false; 
	}
	
	
	/**
	* END VALIDATION CHECKS
	*/
	
	/**
  * Checks weither a record exists or not
  * @param string $col Column name you wish to check
  * @param string $value Value you wish to check aginst
  * @todo add multi conditional support
  */
  public static function exists($col, $value) {
    $sql = 'SELECT 1 from ' . static::table_name() . ' WHERE (`' . static::sanatize_input_array($col) . '`= ' . "'" . static::sanatize_input_array($value) . "') LIMIT 0,1";
    $result = static::execute($sql);
    $return = mysql_fetch_assoc($result);
    if(isset($return['1'])) {
      return true;
     }else{
      return false;
     }
  }
	
	
	
	/**
	* START CREATE METHODS
	*/
	
	/** Create Callbacks*/
	public function before_create() {}
	public function after_create()  {}
	
	public static function create($attributes = array()) {
		$klass = new static::$class;
		$klass->row = array_merge($klass->row, $attributes);
		static::getErrors($klass);
    call_user_func_array(array($klass, 'before_create'), array());
		$klass->row = self::update_timestamps(array('created_at', 'updated_at'), $klass->row);
		$sql = 'INSERT INTO ' . self::table_name() ;
		$keys = array_keys($klass->row);
		$values = array_values($klass->row);
		$clean = self::sanatize_input_array($values);
		$keys = self::sanatize_input_array($keys);
		$clean = static::prepair_nulls($clean);
		$sql .= " (`" . join("`, `", $keys) . "`) VALUES (" .  join(", ", $clean) . ");";
		if(count($klass->errors) == 0 && self::execute_insert_query($sql)) {
			array_push(self::$query_log, "CREATE: $sql");
			$klass->row['id'] = static::insert_id();
			$klass->saved = true;
			call_user_func_array(array($klass, 'after_create'), array());
			return $klass;
		}else{	
			$klass->saved = false;
			return $klass;
		}
		
	}
	
	public static function _create($attributes = array()) {
		$create = self::create($attributes);
		if(count($create->errors)) {
			throw new \ActivePhp\ActivePhpException(join("\n", $create->errors[0]));
		}
		if(!$create->saved){
			throw new \ActivePhp\ActivePhpException('Failed to create record');
		}
		
		return $create;
	}
	
	
	private static function insert_id() {
		return mysql_insert_id(static::$connection);
	}
	
	
	private static function update_timestamps($timestamp_cols, $attributes) {
		$columns = self::columns();
		$time = \ActiveSupport\DateHelper::to_string('db', time());
		foreach($timestamp_cols as $ts) {
			if(in_array($ts, $columns)) {
				$attributes = array_merge($attributes, array($ts => $time));
			}
		}
		return $attributes;
	}
	
	/**
	* END CREATE METODS
	*/
	
	
	/**
	* START UPDATE METHODS
	*/
	
	//Update callbacks
	public function before_update() {}
	public function after_update() {}
	
	public static function update($id, $attributes = array()) {
		$klass = self::_find($id);
		$old_row = $klass->row;
		$klass->update_mode = true;
    $klass->row = array_merge($klass->row, $attributes);
    static::getErrors($klass);
    call_user_func_array(array($klass, 'before_update'), array());
		$sql = 'UPDATE ' . self::table_name() . ' SET ';
		$updates = array();
		$attributes = self::update_timestamps(array('updated_at'), $attributes);
		foreach ($klass->row as $key => $value) {
	  		array_push($updates, '`' . self::sanatize_input_array($key) . "` = " . $clean = static::prepair_nulls(self::sanatize_input_array($value)) . "");
		}
		$sql .= join(", ", $updates) . ' WHERE `id` = ' . self::sanatize_input_array($id);
		if(count($klass->errors) == 0 && self::execute_insert_query($sql)) {
			array_push(self::$query_log, "UPDATE: $sql");
      $klass->id = $id;
			$klass->saved = true;
			$klass->update_mode = false;
			call_user_func_array(array($klass, 'after_update'), array());
			return $klass;
		}else{
			$klass->saved = false;
			$klass->update_mode = false;
			return $klass;
		}
	
	}
	
	public static function _update($id_or_array, $attributes = array()) {
		$update = self::update($id_or_array, $attributes);
		if(!$update->saved) {
			throw new \ActivePhp\ActivePhpException('Failed to update record');
		}
		return $update;
	}
		
		
	protected static function prepair_nulls($array) {
		if(is_array($array)) {
			foreach(array_keys($array) as $key) {
				if(is_null($array[$key]) || $array[$key] == null) {
					$array[$key] = 'NULL';
				}else{
					$v = $array[$key];
					$array[$key] = "'{$v}'";
				}
			}
		}else{
			if(is_null($array) || $array == null) {
				$array = 'NULL';
			}else{
				$v = $array;
				$array = "'{$v}'";
			}
		}
		return $array;
	}	
		
		
	/**
	* END UPDATE METODS
	*/

	/**
	* START PRIVATE UTILITY METHODS
	*/
	/**
	* Method load_columns
	* Fetches all the columns from this table for validations and packs them into a cached array
	*/
	private static function load_columns() {
		$sql = 'SHOW COLUMNS FROM ' . self::table_name();
		if (isset(self::$query_cache[md5(strtolower($sql))])) {
			array_push(self::$query_log, "CACHED: $sql");
			return self::$query_cache[md5(strtolower($sql))];
		}else{
			$result = mysql_query($sql, self::connection());
			array_push(self::$query_log, $sql);
			$output = array();
			while($row = mysql_fetch_assoc($result)) {
				array_push($output, $row);
			}
			self::$query_cache[md5(strtolower($sql))] = $output;
			return $output;
		}
	}
	/**
	* Method column array
	* Builds a 1 demisional array of column names 
	*/
	private static function column_array() {
		$columns = self::load_columns();
		$output = array();
		foreach($columns as $column) {
			array_push($output, $column['Field']);
		}
		return $output;
	}
	/**
	* Method column_validations
	* Builds and array of required columns based on if the column is allowed to be null or not
	*/
	private static function column_validatons($klass) {
		$columns = self::load_columns();
		foreach($columns as $column) {
			if (strtolower($column['Null']) == 'no' && strtolower($column['Field'] != self::$primary_key_field) && !preg_match('/_id$/', strtolower($column['Field']))){
					if(empty($klass->row[strtolower($column['Field'])])) {
						$col = ucwords($column['Field']);
						array_push($klass->errors, array(strtolower($column['Field']) => "{$col} can not be blank"));
					}
			}
		}
	}
	
	/**
	* Method columns
	* Sets the static variable self::$columns with the results from self::column_array()
	* @param $override Boolean - will reload the columns
	*/
	private static function columns($override = false) {
		self::$columns = self::column_array();
		return self::$columns;
	}

  	/**
	* Method execute_query
	* use self::execute_query('SELECT * FROM `foo` WHERE `foo`.id = 1', false)
	* @param sql String
	* @param all Boolean - true = multiresults | false = single result
	*/
	public static function execute_query($sql, $all = true, $cache = true){
		//fetch query cache if it exsists
		if ($cache && isset(self::$query_cache[md5(strtolower($sql))])) {
			array_push(self::$query_log, "CACHED: $sql");
			return self::$query_cache[md5(strtolower($sql))];
		}else{
			//execute query and set cache pointer
			array_push(static::$query_log, $sql);
			$result = static::execute($sql);
			$return =  $all ? static::to_objects($result) : static::to_object(mysql_fetch_assoc($result));
			if($cache) {
				self::$query_cache[md5(strtolower($sql))] = $return;
			}
			if($result) {
				mysql_free_result($result);
			}
			return $return;
		}
	}
	
	
	public static function start_transaction() {
		static::execute('BEGIN;');
	}
	
	public static function end_transaction() {
		static::execute('ROLLBACK;');
	}
	
	public static function execute_insert_query($sql) {
		$return = static::execute($sql);
		return $return;
	}
	
	
	
	public static function disable_referential_integrity($reset=false) {
		if(!$reset) {
			$old = static::select_one("SELECT @@FOREIGN_KEY_CHECKS");
			static::$temp['old_fk'] = reset($old);
			static::execute("SET FOREIGN_KEY_CHECKS = 0");
		}else{
			static::execute("SET FOREIGN_KEY_CHECKS = " . static::$temp['old_fk']);
			unset(static::$temp['old_fk']);
		}
		
	}
	/**
	* executes a single query returns the result object;
	* @param $sql 
	*/
	public static function execute($sql, $reset=false) {
		if($reset) {
			mysql_select_db(static::$database, static::connection());
		}
		if(static::test_mode()){
			echo $sql . "\n\n";
		}
		return  mysql_query($sql, static::connection());
	}
	
	/**
	* executes a scaler returning a single row
	* @param $sql 
	*/
	public static function select_one($sql) {
		$result = self::execute($sql);
		return mysql_fetch_assoc($result);
	}
	
	
	/**
	* Method to_objects
	* use self::to_objects($result)
	* @param array $result_set_array
	*/
	private static function to_objects($result_set_array) {
		$object_list = array();
		while($result_set = mysql_fetch_assoc($result_set_array)) {
		  array_push($object_list, self::to_object($result_set));
		}
		return new ResultCollection($object_list);
	}
  	/**
	* Method to_object
	* use self::to_object($result_set)
	* @param array $result_set 
	*/
  private static function to_object($result_set) {
		if (empty($result_set)){
			throw new \ActivePhp\RecordNotFound();
		}
	$object = new static::$class;
	$object->from_array($result_set);
	return $object;
  }
	/**
	* Method build_where_from_array
	*	use self::build_where_from_array(array())
	* @param $conditions Array - array('id' => 3, 'name' => 'bob')
	*/
	private static function build_where_from_array($conditions){
		$sql = 'WHERE(';
		foreach($conditions as $condition => $value){
			$sql .= self::table_name() . '.' . $condition. " = '" . mysql_real_escape_string($value) ."'";
		}
		$sql .= ')';
		return $sql;
	}
  
  /* Instance Methods */
  protected $row;

  public function __construct($args = array()) {
  $this->errors = array();
  $this->row = $args;
  }
  /**
  * Method __toString
  * @access public
  * returns NULL or record id
  */
  public function __toString(){
    if (isset($this->row['id'])){
      return (string) $this->row['id'];
    }else{
      return NULL;
    }
  }
  /**
	* Method from_array
	* @access private
	* @param $result_set Array
	* sets the global row value to a result array;
	*/
	private function from_array($result_set) {
		$this->row = $result_set;
	}


	/**
	* Work in progress
	*/
	public function save() {    
	if(!isset($this->row) || 0 == count($this->row)) {
	  throw new \Exception("Can't an save empty record.");
    }
    if($this->is_new_record()) {
      if($class = static::create($this->row)) {
        $this->row = $class->row;
      }else{
        $this->errors = $class->errors;
      }
    }else{
      if(!isset($this->row[static::primary_key_field()])) {
        throw new \Exception("Primary key not set for row, cannot save.");
      }
      $f = self::primary_key_field();
      $primary_key_value = $this->row[$f];
      unset($this->row[$f]);
      if($class = static::update($primary_key_value, $this->row)) {
        $this->row = $class->row;
      }else{
        $this->errors = $class->errors;
      }
    }
  }
    
	
	public function process_error_return($return) {
		if(!$return[0]) {
			array_push($this->errors, array($return[1] => $return[2]));
		}
	}
	/**
	* MAGIC METHODS
	*/
	
	public function __get($var) {
		if(isset($this->row[$var])) {
			return $this->row[$var];
		} elseif(is_null($this->row[$var]) && in_array($var, static::columns())){
			return null;
		}elseif($this->association_has_many_exists($var)) {
			return $this->association_has_many_find($var);
		}elseif($this->association_has_many_polymorphic_exists($var)) {
			return $this->association_has_many_polymorphic_find($var);
		} elseif($this->association_belongs_to_exists($var)) {
			return $this->association_belongs_to_find($var);
		} else {
			throw new \Exception("Property not found in record.");
		}
	}
	
	
	public function __set($var, $value) {
		if(in_array($var, static::columns())) {
			$this->row[$var] = $value;
		}else{
			throw new \Exception("Can not set property it does not exsit.");
		}
	}
	
	
	public function __call($method, $arguments)  {
		if(in_array($method, static::columns())) {
			$this->row[$method] = $arguments;
		}
		/**This is a special case because we do not want uniqueness_of being called on an update
		 * sice it already exsists... because we are updating it!
		 */
		if($this->update_mode && preg_match('/uniqueness_of/', $method)) {
			return;
		}
		
		if(preg_match('/^validates_[0-9a-z_]+/', $method, $matches)) {
			$klass_method = str_replace('validates_', '', $method);
			if(in_array($klass_method, get_class_methods('Validate'))) {
				if(count($arguments[0]) > 1){
					foreach($arguments[0] as $column) {
            if(!isset($this->row[$column])) {
              $value = '';
            }else{
              $value = $this->row[$column];
            }
						$args = array('column_name' => $column, 'value' => $value);
						if(isset($arguments[1]) && !empty($arguments[1])) {
							$args = array_merge($args, $arguments[1]);
						}
						$return = call_user_func_array(array('Validate', $klass_method), array($args));
						$this->process_error_return($return);
					}
				}else{
					$column = $arguments[0][0];
					$args = array('column_name' => $column, 'value' => $this->row[$column]);
					if(isset($arguments[1]) && !empty($arguments[1])) {
						$args = array_merge($args, $arguments[1]);
					}
					$return = call_user_func_array(array('Validate', $klass_method), array($args));
					$this->process_error_return($return);
				}
			}
		}	
	}
	
	public static function __callStatic($method, $args) {
		if(preg_match('/^find_by_([a-z0-9_]+)$/', $method, $matches)) {

			$method_called = array_shift($matches);
			$i = 0;
			$where = array();
			$cols = preg_match('/_and_/', $matches[0]) ? explode('_and_', $matches[0]) : $matches;
			foreach($cols as $column) {
				if(in_array($column, static::columns())) {
					$col = self::sanatize_input_array($column);
					$val = self::sanatize_input_array($args[$i]);
					array_push($where, "$col = '$val'");
					$i++;
				}
			}
			return static::find_by(array('conditions' => join(' AND ', $where)));
		}
	}

	/**
	* END MAGIC METHODS
	*/
	
	
	public function is_new_record() {
		if(isset($this->row) && (count(static::columns()) == count(array_keys($this->row)))) {
			return true;
		}else{
			return false;
		}
	}
	
	public function is_set($name) {
		return isset($this->row) && isset($this->row[$name]);
	}
	
	
	/**
	* PROTECTED UTILITY METHODS
	*
	*/

	protected function association_has_many_exists($association_name) {
		$associations = $this->associations();
		if (isset($associations['has_many'])){
			return isset($associations['has_many'][$association_name]) || in_array($association_name, $associations['has_many']);
		}else{
			return false;
		}
	}
	
	protected function association_has_many_polymorphic_exists($association_name) {
		$associations = $this->associations();
		if (isset($associations['has_many_polymorphic'])){
			return isset($associations['has_many_polymorphic'][$association_name]) || in_array($association_name, $associations['has_many_polymorphic']);
		}else{
			return false;
		}
	} 

	protected function association_belongs_to_exists($association_name) {
		$associations = $this->associations();
		if(isset($associations['belongs_to'])){
			return isset($associations['belongs_to'][$association_name]) || in_array($association_name, $associations['belongs_to']);
		}else{
			return false;
		}
	}

	protected function association_foreign_key($association_name) {
		$associations = $this->associations();
		return strtolower(static::$class) . '_id';
	}

	protected function association_table_name($association_name) {
		$name = $this->association_model($association_name);
		return strtolower(call_user_func("$name::table_name"));
	}

	protected function association_model($association_name) {
		return \Inflector::classify($association_name);
	}

	protected function association_has_many_find($association_name) {
		$primary_key_field = static::primary_key_field();
		$primary_key_value = mysql_real_escape_string($this->row[$primary_key_field]);
		$conditions = $this->association_table_name($association_name) . '.' . $this->association_foreign_key($association_name) . ' = ' . $primary_key_value;
		$association_model = $this->association_model($association_name);
		$find_array = call_user_func("$association_model::find_all", 
		  array('conditions' => $conditions)
		);
		return $find_array;
	}
	
	
	protected function association_has_many_polymorphic_find($association_name) {
		$association_model = $this->association_model($association_name);
		$singular = \Inflector::singularize($association_name);
		$polymorphic_column_type = $singular . 'able_type';
		$polymorphic_column_id =  $singular . 'able_id';
		$class = strtolower(get_class($this));
		$conditions = $polymorphic_column_type . " = '$class' AND " . $polymorphic_column_id . " = '{$this->id}'";
		return call_user_func("$association_model::find_all", array('conditions' => $conditions));
	}

	protected function association_belongs_to_find($association_name) {
		$primary_key_value = mysql_real_escape_string($this->row[$association_name . '_id']);
		$association_model = $this->association_model($association_name);
		return call_user_func("$association_model::find", $primary_key_value);
	}



	public function to_xml($include_head = true) {
		$xw = new \xmlWriter();
	$xw->openMemory();

		if ($include_head){
			$xw->startDocument('1.0','UTF-8');
			$xw->startElement(strtolower(static::$class));
		}
		foreach($this->row as $key => $value) {
		$xw->writeElement($key, $value);
		}	
		if ($include_head){
			$xw->endElement(); 
		}
		return $xw->outputMemory(true);
	}
	
	
  
}
