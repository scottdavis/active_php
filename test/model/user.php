<?php
class User extends ActivePhp\Base {
	public static $class = __CLASS__;
	public static $associations = array('has_many' => array('photos'));
	public static $callback_tests = array();
	
	public function validators_for_name($col) {
		$this->validates_presence_of($col);
		$this->validates_uniqueness_of($col, array('class' => $this));
	}
	
	public function validators_for_my_int($col) {
		$this->validates_numercality_of($col);
	}
	
	
	public function before_create() {
		static::$callback_tests['before_create'] = true;
	}
	
	public function after_create() {
		static::$callback_tests['after_create'] = true;
	}
	
	public function before_update() {
		static::$callback_tests['before_update'] = true;
	}
	
	public function after_update() {
		static::$callback_tests['after_update'] = true;
	}

	public function before_validation() {
		static::$callback_tests['before_validation'] = true;
	}
	
	public function after_validation() {
		static::$callback_tests['after_validation'] = true;
	}
	
	
}
?>