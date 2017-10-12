<?php
namespace advert\widgets\premium;

/**
* Created by FALI Crea.
* User: Tiafeno Finel
* Date: 12/02/2017
* Time: 01:57 PM
*/

/**
* @property TWIG Engine
*/

class Premium_Widget extends \WP_Widget{

  private $Template;

  public function __construct(){
    global $twig;
		if (is_null( $twig )){
			print 'Active or install Template Engine TWIG';
			return;
    }
    
    $this->Template = $twig;
    parent::__construct("premium_advert", "Advert > Premium Box", array('description' => ''));
  }

  public function widget($args, $instance){
    
  }

  public function form($instance){

  }
}
