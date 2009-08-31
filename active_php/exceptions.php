<?php
	namespace ActivePhp;
	class ActivePhpException extends \Exception {}
	class RecordNotFound extends \Exception { }
	class StatementInvalid extends \Exception {
		protected $exeception;
		
		public function __construct($exception){
			$this->exception = $exeception;
			parent::__construct($exeception);
		}
		
		public function __toString(){
			return $this->exception;
		}
	}
?>