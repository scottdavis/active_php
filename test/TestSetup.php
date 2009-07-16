<?php
/*
* This file loads the database so tests can be run
*/
require_once(dirname(__FILE__) . '/../active_support/inflector.php');
require_once(dirname(__FILE__) . '/../base.php');
require_once(dirname(__FILE__) . '/TestMigration.php');
require_once(dirname(__FILE__) . '/model/user.php');
require_once(dirname(__FILE__) . '/model/photo.php');
require_once(dirname(__FILE__) . '/TestData.php');



	/**
	* Similar to rubys collect method
	* @param function $func
	* @param array|interator $array
	* @uses collect(function($value){return $value+1}, range(1,5));
	*/
	function collect($func, $array) {
	  $out = array();
	  foreach($array as $value) {
	    array_push($out, $func($value));
	  }
	  return $out;
	}


	require_once(dirname(__FILE_) . '/config/db_connect.php');

	//load the test database
	if(!defined('DATABASE_CREATED')) {
		$m = new Migration();
		$m->drop_database(MYSQL_DATABASE);
		$m->create_database(MYSQL_DATABASE);
		define('DATABASE_CREATED', true);
	}
	
	//load the table if it hasn't been loaded
	
	if(!defined('TABLE_CREATED')) {
		$g = new TestMigration();
		$g->down();
		$g->up();
		define('TABLE_CREATED', true);
	}
	
	load_test_data();

	foreach(array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie') as $user) {
		$func_name = strtolower($user);
		$func = "function $func_name() {
			return User::_find_by(array('conditions' => \"name = '" . $user . "'\"));
		};";
		eval($func);
	}



?>