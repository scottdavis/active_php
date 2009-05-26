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
define('MYSQL_DATABASE', 'active_php_test');
define('DATABASE_HOST', 'localhost');


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


	ActivePhp\Base::establish_connection(array(
	  'host' => DATABASE_HOST,
	  'database' => MYSQL_DATABASE,
	  'username' => 'root',
	  'password' => ''
	));

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

?>