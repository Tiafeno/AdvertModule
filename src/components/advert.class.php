<?php
namespace advert\src\components;
use advert\src\interfaces as Interfaces;

class AdvertComponent implements Interfaces\AdvertInterface {
  protected $user_id;
  public $User;
  
  public function __construct( $ID ) 
  { 
    if (!is_int( $ID )) return false;
    $this->user_id = &$ID;
  }

  public function getUser() {

  }
}