<?php
namespace ActiveSupport;

class TimeHelper {
	
	public static $formats = array(
			'db' => 'Y-m-d H:m:s',
			'standard' => 'm/d/y g:i a',
			'full' => 'F j, Y, g:i a',
			'rfc822' => 'D, d M Y H:m:s Z'
		);
		
		
	public static function to_string($format, $timestamp) 
	{
		
			$formats = self::$formats;
			if(!empty($format[$formats]) && !isset($formats[$format])){
				throw new \Exception('Invaild Time output format');
			}
			if (!empty($timestamp) && !isset($timestamp)){
				throw new \Exception('Invalid Time');
			}
			
			return date($formats[$format], $timestamp);
	}
	
	/**
	* Barrowed from The Symphony Project
	*/

	public static function distance_of_time_in_words($from_time, $to_time, $include_seconds = false)
		{
		  $distance_in_minutes = abs(round(($to_time - $from_time) / 60));
		  $distance_in_seconds = abs(round(($to_time - $from_time)));
		
		  if ($distance_in_minutes <= 1)
		  {
		    if (!$include_seconds) return ($distance_in_minutes == 0) ? "less than a minute" : "1 minute";
		    if ($distance_in_seconds <= 5)
		      return "less than 5 seconds";
		    else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
		      return "less than 10 seconds";
		    else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
		      return "less than 20 seconds";
		    else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
		      return "half a minute";
		    else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
		      return "less than a minute";
		    else
		      return "1 minute";
		  }
		  else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 45)
		    return $distance_in_minutes." minutes";
		  else if ($distance_in_minutes >= 46 && $distance_in_minutes <= 90)
		    return "about 1 hour";
		  else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1440)
		    return "about ".round($distance_in_minutes / 60)." hours";
		  else if ($distance_in_minutes >= 1441 && $distance_in_minutes <= 2880)
		    return "1 day";
		  else
		    return round($distance_in_minutes / 1440)." days";
		}
		
		# Like distance_of_time_in_words, but where <tt>to_time</tt> is fixed to <tt>Time.now</tt>.
		public function time_ago_in_words($from_time, $include_seconds = false)
		{
		  return distance_of_time_in_words($from_time, time(), $include_seconds);
		}
}

?>