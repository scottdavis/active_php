<?php
namespace ActiveSupport;

class DateHelper {
	
	public static $formats = array(
			'db' => 'Y-m-d',
			'standard' => 'm/d/y',
			'full' => 'F j, Y, g:i a',
			'rfc822' => 'd M Y'
		);
	
	
	public static function to_string($format, $timestamp) 
	{
		
		 $formats = self::$formats;
			if(!empty($format[$formats]) && !isset($formats[$format])){
				throw new \Exception('Invaild Date output format');
			}
			if (!empty($timestamp) && !isset($timestamp)){
				throw new \Exception('Invalid Date');
			}
			
			return date($formats[$format], $timestamp);
	}
	
}