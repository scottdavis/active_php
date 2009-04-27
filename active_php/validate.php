<?php

	class Validate {
	
		public $default_error_messages = array(
			'inclusion' => "is not included in the list",
			'exclusion' => "is reserved",
			'invalid' => "is invalid",
			'confirmation' => "doesn't match confirmation",
			'accepted ' => "must be accepted",
			'empty' => "can't be empty",
			'blank' => "can't be blank",
			'too_long' => "is too long (max is %d characters)",
			'too_short' => "is too short (min is %d characters)",
			'wrong_length' => "is the wrong length (should be %d characters)",
			'taken' => "has already been taken",
			'not_a_number' => "is not a number",
			'not_an_integer' => "is not an integer"
		);
	
	
	
		public static function acceptance_of($args = array('column_name', 'value')) {
			$defaults = array('message'=> 'must be accepted');
			$args = array_merge($defaults, $args);
			 if(!$args['value']) {
				return self::false_result($args['column_name'], $args['message']);	
            }else{
				return array(true);
			}
		}
		
		
		public static function confirmation_of($args = array('column_name', 'value1', 'value2')) {	
            $defaults = array('message' => "doesn't match confirmation");
			$args = array_merge($defaults, $args);
			$attribute_confirmation = $args['attribute_name'] . '_confirmation';
            if($args['value1'] !== $args['value2']) {
				return self::false_result($args['column_name'], $args['message']);
            }else{
				return array(true);
			}
        }
		
		/**
		 * Validates that attributes are NOT in an array.
		 * eg. $this->validates_exclusion_of($callback, array(13, 19));
		 * eg. $this->validates_exclusion_of($callback, range(13, 19));
		 * @see range()
		 */
		public static function exclusion_of($args = array('column_name', 'value', 'in' => array())) {				
			$defaults = array('message' => 'is reserved');
			$args = array_merge($defaults, $args);
			
			if(in_array($value, $in)) {
				return self::false_result($column_name, $message);
			}else{
				return array(true);
			}
        }
			
		/**
		 * Validates that attributes are in an array.
		 * eg. $this->validates_inclusion_of($callback, array(13, 19));
		 * eg. $this->validates_inclusion_of($callback, range(13, 19));
		 * @see range()
		 */
		public static function inclusion_of($args = array('column_name', 'value', 'in' => array())) {	
			$defaults = array('message' => 'is not included in the list');
			$args = array_merge($defaults, $args);
			if(!in_array($args['value'], $args['in'])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
        }
		
		
		
		public static function format_of($args = array('column_name', 'value', 'regex')) {						
			$defaults = array('message' => 'is invalid');
			$args = array_merge($defaults, $args);
			if(!preg_match($args['regex'], $args['value'])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
        }
		
		public static function length_of($args = array('column_name', 'value', 'length')) {
			$defaults = array('message' => 'is the wrong length');
			$args = array_merge($defaults, $args);	
			if(is_array($args['length'])) {
				if(!in_array(strlen($args['value']))){
					return self::false_result($args['column_name'], $args['message']);
				}else{
					return array(true);
				}
			}else{
				(int) $l = $length;
				if(strlen($value) !== $l){
					return self::false_result($args['column_name'], $args['message']);
				}else{
					return array(true);
				}
			}
		
		}
		
		public static function numercality_of($args = array('column_name', 'value')) {
			$defaults = array('message' => 'must be an integer');
			$args = array_merge($defaults, $args);
			if(is_numeric($args['value']) || empty($args['value'])) {
				return array(true);
			}else{
				return self::false_result($args['column_name'], $args['message']);
			}
		}
		
		
		public static function presence_of($args = array('column_name', 'value')) {
			$defaults = array('message' => "can not be blank");
			$args = array_merge($defaults, $args);
			if(empty($args['value'])) {
				return self::false_result($args['column_name'], $args['message']);
			}else{
				return array(true);
			}
		}
		
		
		
		
		
		private static function false_result($column_name, $message) {
			return array(false, $column_name, Inflector::humanize($column_name) . ' ' . $message);
		}
		
		
    }


?>