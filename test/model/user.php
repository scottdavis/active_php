<?php
class User extends ActivePhp\Base {
	protected static $class = __CLASS__;
	public static $associations = array('has_many' => array('photos'));
	
	
	public function validators_for_name($col) {
		$this->validates_presence_of($col);
		$this->validates_uniqueness_of($col, array('class' => $this));
	}
	
	public function validators_for_my_int($col) {
		$this->validates_numercality_of($col);
	}
	
}
?>