<?php
	$HELPERS_LOADED = false;
	$uSeRs = array();

	function load_test_data() {
		$users = array('Tim', 'Steve', 'Joe', 'Bob', 'John', 'Scott', 'Randy', 'Jessica', 'Julie');
		Photo::truncate();
		User::truncate();
		foreach($users as $user) {
			$_user = User::_create(array('name' => $user, 'my_int' => ''));
			foreach(range(0,100) as $i) {
				Photo::_create(array('user_id' =>$_user->id,'title' => 'photo_' . $i));
			}
		}

	}
	
	
	
	
?>