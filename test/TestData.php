<?php
	
	$uSeRs = array();

	function load_test_data() {
		$users = array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie');
		Photo::truncate();
		User::truncate();
		ActivePhp\Base::$test_mode = true;
		foreach($users as $user) {
			$_user = User::_create(array('name' => $user, 'my_int' => ''));
			foreach(range(0,100) as $i) {
				Photo::_create(array('user_id' =>$_user->id,'title' => 'photo_' . $i));
			}
		}
		ActivePhp\Base::$test_mode = false;
		if(!defined('HELPERS_LOADED')) {
			load_helper_functions();
			define('HELPERS_LOADED', true);
		}
	}
	
	
	function load_helper_functions() {
		$users = array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie');
		foreach($users as $user) {
			$func_name = strtolower($user);
			$string = "function $func_name() {
				global \$uSers;
				if(!isset(\$uSeRs[$func_name])) {
					\$uSeRs[$func_name] = User::_find_by(array('conditions' => \"name = '" . $user ."'\"));
				} 
				return \$uSeRs[$func_name];
				}";
			eval($string);
		}
	}
	
	
	
?>