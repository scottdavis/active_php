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
		$name = strtolower(isset(static::$table_name) ?: \Inflector::pluralize(static::$class));
	return "`$name`";
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

	protected static function database() {
	return static::$database;
	}
	
	public function class_name() {
		return static::$class;
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
			(string) $string = $input;
			return mysql_real_escape_string($string);
		}
	}
	
	
	/**
	* MATH METHODS
	*/
	
	/**
	* use self::check_args_for_math_functions($options)
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
		return self::execute_query($sql, false)->count_all();
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
		return self::execute_query($sql, false)->sum_all();
	}
	/**
	* Method max
	* use Class::max(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function max($options = array('column' => NULL, 'conditions' => NULL)) {
		self::check_args_for_math_functions($options);
		$sql = 'SELECT max('. self::table_name() . '.' . $options['column'] . ') as max_all FROM ' . self::table_name();
		$sql .= isset($options['conditions']) ? self::build_conditions($options['conditions']) : '';
		return self::execute_query($sql, false)->max_all();
	}
	/**
	* Method min
	* use Class::min(array('column' => 'name', 'conditions' => array('id' => 1)))
	* @param $options Array
	*/
	public static function min($options = array('column' => NULL, 'conditions' => NULL)) {
		self::check_args_for_math_functions($options);
		$sql = 'SELECT min('. self::table_name() . '.' . $options['column'] . ') as min_all FROM ' . self::table_name();
		$sql .= isset($options['conditions']) ? self::build_conditions($options['conditions']) : '';
		return self::execute_query($sql, false)->min_all();
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
	* @param id_or_array Array || String
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
		$sql = 'SELECT * from ' . self::table_name() .  
		(isset($options['conditions'])  ? ' WHERE ' . $options['conditions']  : '') . 
		(isset($options['limit'])       ? ' LIMIT ' . $options['limit']       : '') . ';';
		return self::execute_query($sql, true);
	}
	
	/**
	* END FIND METHODS
	*/
	
	/**
	* START CREATE METHODS
	*/
	//INSERT INTO `forums` (`name`, `topics_count`, `aasm_state`, `description`, `posts_count`, `position`, `school_id`) VALUES('Jobs', 0, 'active', 'Talk about job postings', 0, 2, 1)
	
	public static function create($attributes = array()) {
		self::column_validatons();
		self::run_validations($attributes);
		$attributes = self::update_timestamps(array('created_at', 'updated_at'), $attributes);
		$sql = 'INSERT INTO ' . self::table_name() ;
		$keys = array_keys($attributes);
		$values = array_values($attributes);
		$clean = self::sanatize_input_array($values);
		$keys = self::sanatize_input_array($keys);
		$sql .= " (`" . join("`, `", $keys) . "`) VALUES ('" .  join("', '", $clean) . "');"; 
		array_push(self::$query_log, "CREATE: $sql");
		if(self::execute($sql)) {
			return self::to_object(array_merge(array('id' => self::insert_id()), $attributes));
		}else{
			return false;
		}
		
	}
	
	public static function _create($attributes = array()) {
		$create = self::create($attributes);
		$errors = self::run_validations($attributes);
		if(!$create){
			
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
	
	public static function update($id, $attributes = array()) {
		$sql = 'UPDATE ' . $tbl_name . ' SET ';
		$updates = array();
		$attributes = self::update_timestamps(array('updated_at'), $attributes);
		foreach ($attributes as $key => $value) {
			if(empty($value)) {
				continue;
			}
	  		array_push($updates, '`' . self::sanatize_input_array($key) . "` = '" . self::sanatize_input_array($value) . "'");
		}
		$sql .= join(", ", $updates) . ' WHERE `id` = ' . self::sanatize_input_array($id);
		array_push(self::$query_log, "UPDATE: $sql");
		if(self::execute($sql)) {
			return self::_find($id);
		}else{
			return false;
		}
	
	}
	
	public static function _update($id_or_array, $attributes = array()) {
		$update = self::update($id_or_array, $attributes);
		if(!$update) {
			throw new \ActivePhp\ActivePhpException('Failed to update record');
		}
		return $update;
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
	private static function column_validatons() {
		$columns = self::load_columns();
		$not_null = array();
		foreach($columns as $column) {
			if (strtolower($column['Null']) == 'no' && strtolower($column['Field'] != self::$primary_key_field)){
				array_push($not_null, $column['Field']);
			}
		}
		return $not_null;
	}
	
	/**
	* Method columns
	* Sets the static variable self::$columns with the results from self::column_array()
	* @param $override Boolean - will reload the columns
	*/
	private static function columns($override = false) {
		if (!$override){
			if (isset(self::$columns) && !empty(self::$columns)) {
				return self::$columns;
			}else{
				self::$columns = self::column_array();
			}
		}
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
			array_push(self::$query_log, $sql);
			$result = self::execute($sql);
			$return =  $all ? self::to_objects($result) : self::to_object(mysql_fetch_assoc($result));
			if($cache) {
				self::$query_cache[md5(strtolower($sql))] = $return;
			}
			if($result) {
				mysql_free_result($result);
			}
			return $return;
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
		return mysql_query($sql, self::connection());
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
	* @param result_set_array ResultSet
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
	* @param result_set AssocArray
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
  
	  public function __construct() {
    
	  }
		/**
		* Method __toString
		* @access public
		* returns NULL or record id
		*/
		public function __toString(){
			if (isset($this->row)){
				return $this->row['id'];
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
	  throw new \Exception("Can't save empty record.");
	}

	$sql_fields = '';
	foreach($this->row as $key => $value) {
	  $value = mysql_real_escape_string($value);
	  $sql_fields .= $key . ' = ' . $value . ', ';
	}
	$sql_fields = substr($sql_fields, 0, strlen($sql_fields) - 2);
	if(!isset($this->row[static::primary_key_field()])) {
	  throw new \Exception("Primary key not set for row, cannot save.");
	}
	$primary_key_value = $this->row[static::primary_key_field()];

	$sql = 'UPDATE ' . static::table_name() . ' SET ' . $sql_fields . 
	  ' WHERE ' . static::primary_key_field() . ' = ' . $primary_key_value;

	return mysql_query($sql, $this->database());
	}
    
	public function __get($var) {
		if(isset($this->row[$var])) {
			return $this->row[$var];
		} elseif($this->association_has_many_exists($method)) {
			return $this->association_has_many_find($method);
		} elseif($this->association_belongs_to_exists($method)) {
			return $this->association_belongs_to_find($method);
		} else {
			throw new \Exception("Property not found in record.");
		}
	}
	
	
	public function __call($method, $arguments) {
		if(in_array($method, self::columns())) {
			$this->row[$method] = $arguments;
		}
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
		return ucwords(\Inflector::singularize($association_name));
	}

	protected function association_has_many_find($association_name) {
		$primary_key_field = static::primary_key_field();
		$primary_key_value = mysql_real_escape_string($this->$primary_key_field());
		$conditions = $this->association_table_name($association_name) . '.' . $this->association_foreign_key($association_name) . ' = ' . $primary_key_value;
		$association_model = $this->association_model($association_name);
		$find_array = call_user_func("$association_model::find", 
		  array('conditions' => $conditions)
		);
		return $find_array;
	}

	protected function association_belongs_to_find($association_name) {
		$primary_key_field = static::primary_key_field();
		$primary_key_value = mysql_real_escape_string($this->row[$association_name . '_id']);
		$conditions = $this->association_table_name($association_name) . '.' . $primary_key_field . ' = ' . $primary_key_value;
		$association_model = $this->association_model($association_name);
		$find_array = call_user_func("$association_model::find", 
		  array('conditions' => $conditions)
		);
		return $find_array;
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
