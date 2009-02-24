<?php

class User extends ActivePhp\Base {
  protected static $class = __CLASS__;
  
  public static $associations = array(
    'has_many' => array(
      'photos'
    )
  );
};

