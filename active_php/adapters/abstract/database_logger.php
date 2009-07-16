<?php
namespace ActivePhp;

	class DatabaseLogger {
		var $dump_to_file = false;
		var $even_row = true;
		public function __construct() {

		}
		
		
		public function add_message($string) {
			if($dump_to_file) {
				$this->format_log_entry($string);
			}
		}
		
		
		public function format_log_entry($message) {
			if($this->even_row) {
				$this->even_row = true;
				$message_color = "4;36;1";
			}else{
				$this->even_row = false;
				$message_color = "4;35;1";
			}
			
			return "  \e[{$message_color}m#{$message}\e[0m   ";
		}
		
		
		
		
		
		
		
	}

?>