<?php
	

	function load_test_data() {
		$users = array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie');
		Photo::truncate();
		User::truncate();
		foreach($users as $user) {
			$_user = User::_create(array('name' => $user));
			foreach(range(0,100) as $i) {
				Photo::_create(array('user_id' =>$_user->id,'title' => 'photo_' . $i));
			}
		}
		if(!defined('HELPERS_LOADED')) {
			load_helper_functions();
			define('HELPERS_LOADED', true);
		}
	}
	
	
	function load_helper_functions() {
		$users = array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie');
		foreach($users as $user) {
			$func_name = strtolower($user);
			$string = "function $func_name() { return User::_find_by(array('conditions' => \"name = '" . $user ."'\"));};";
			eval($string);
		}
	}
	
	
	
?>