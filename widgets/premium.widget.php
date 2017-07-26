<?php

/**
* Created by FALI Crea.
* User: Tiafeno Finel
* Date: 12/02/2017
* Time: 01:57 PM
*/

/**
* @property TWIG Engine
*/

class Premium_Widget extends WP_Widget{

  private $Template;

  public function __construct(){
    global $TWIG;
    $this->Template = $TWIG;
    parent::__construct("premium_advert", "Advert > Premium Box", array('description' => ''));
  }

  public function widget($args, $instance){
    
  }

  public function form($instance){

  }
}
