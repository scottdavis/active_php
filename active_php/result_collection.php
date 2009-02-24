<?php
namespace ActivePhp;

	class ResultCollection implements \Iterator {
	 	private $array = array();

		public function __construct($array)
    {
        if (is_array($array)) {
            $this->array = $array;
        }
    }


		public function to_xml($options = array()) {
					$klass = $this->array[0]->class_name();
					$plural = \ActiveSupport\Inflector::pluralize($klass);
					$xw = new \xmlWriter();
			    $xw->openMemory();
			    $xw->startDocument('1.0','UTF-8');
			    $xw->startElement(strtolower($plural)); 
					foreach($this->array as $value) {
						$xw->startElement(strtolower($klass));
			    	$xw->writeRaw($value->to_xml(false));
						$xw->endElement();
					}
			    $xw->endElement(); 
			    $xw->endDtd();
					return $xw->outputMemory(true);
		}
		
		public function __toString() {
			return '';
		}


		public function keys(){
			return array_keys($this->array);
		}
		
		public function columns(){
			return $this->keys();
		}

    public function rewind() {
        reset($this->array);
    }

    public function current() {
        $array = current($this->array);
        return $array;
    }

    public function key() {
        $array = key($this->array);
        return $array;
    }

    public function next() {
        $array = next($this->array);
        return $array;
    }

    public function valid() {
        $array = $this->current() !== false;
        return $array;
    }

		
	}

?>