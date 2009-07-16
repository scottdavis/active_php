<?php
require_once(dirname(__FILE__) . '/../../active_support/inflector.php');
require_once(dirname(__FILE__) . '/../../base.php');
define('MYSQL_DATABASE', 'active_php_test');
define('DATABASE_HOST', 'localhost');
ActivePhp\Base::establish_connection(array(
  'host' => DATABASE_HOST,
  'database' => MYSQL_DATABASE,
  'username' => 'root',
  'password' => '',
	'file' => dirname(__FILE__) . '/test.db',
	'adapter' => 'mysql'
));

?>