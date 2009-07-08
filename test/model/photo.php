<?php
class Photo extends ActivePhp\Base {
	public static $class = __CLASS__;
	public static $associations = array('belongs_to' => array('user'));
	
	public function validators_for_user_id_and_title($col) {
		$this->validates_presence_of($col);
	}
	
	public function validators_for_user_id($col) {
		$this->validates_inclusion_of($col, array('in' => range(0, 13)));
	}
	
}
?>