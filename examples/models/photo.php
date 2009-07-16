<?php

class Photo extends ActivePhp\Base {
  protected static $class = __CLASS__;
  
  public static $associations = array(
    'belongs_to' => array(
      'user'
    	)
		);
};