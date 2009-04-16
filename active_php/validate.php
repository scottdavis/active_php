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
	
	
	
		public static function acceptance_of($column_name, $value, $message='must be accepted') {
			 if(!$value) {
				return self::false_result($column_name, $message);	
            }else{
				return array(true);
			}
		}
		
		
		public static function confirmation_of($column_name, $value1, $value2, $message = "doesn't match confirmation") {	
            $attribute_confirmation = $attribute_name . '_confirmation';
            if($value1 !== $value2) {
				return self::false_result($column_name, $message);
            }else{
				return array(true);
			}
        }
		
		/**
		 * Validates that attributes are NOT in an array.
		 * eg. $this->validates_exclusion_of(array(13, 19));
		 * eg. $this->validates_exclusion_of(range(13, 19));
		 * @see range()
		 */
		public static function exclusion_of($column_name, $value, $in = array(), $message = 'is reserved') {				
			if(in_array($value, $in)) {
				return self::false_result($column_name, $message);
			}else{
				return array(true);
			}
        }
			
		/**
		 * Validates that attributes are in an array.
		 * eg. $this->validates_inclusion_of(array(13, 19));
		 * eg. $this->validates_inclusion_of(range(13, 19));
		 * @see range()
		 */
		public static function inclusion_of($column_name, $value, $in = array(), $message = 'is not included in the list') {				
			if(!in_array($value, $in)) {
				return self::false_result($column_name, $message);
			}else{
				return array(true);
			}
        }
		
		
		
		public static function format_of($column_name, $value, $regex, $message = 'is invalid') {						
			if(!preg_match($regex, $value)) {
				return self::false_result($column_name, $message);
			}else{
				return array(true);
			}
        }
		
		public static function length_of($column_name, $value, $length, $message='is the wrong length') {
			
			if(is_array($length)) {
				if(!in_array(strlen($value)){
					return self::false_result($column_name, $message);
				}else{
					return array(true);
				}
			}else{
				(int) $l = $length;
				if(strlen($value) !== $l){
					return self::false_result($column_name, $message);
				}else{
					return array(true);
				}
			}
		
		}
		
		
		
		
		
		private static function false_result($column_name, $message) {
			return array(false, $column_name, Inflector::humanize($column_name) . ' ' . $message);
		}
		
		
    }

    }
	
	
	}

?>