<?php
include_once plugin_dir_path(__FILE__) . 'instance.get.advertController.class.php';
include_once plugin_dir_path(__FILE__) . 'instance.set.advertController.class.php';
include_once plugin_dir_path(__FILE__) . 'instance.edit.advertController.class.php';

abstract class AdvertController {
  public $getter;
  public $setter;
  public $edit;

  public function __construct(){
    $this->getter = new getterAdvertController();
    $this->setter = new setAdvertController();
    $this->edit = new editAdvertController();
  }

  public function getParentsTermsCat(){
    $ctgs = array();
    $parent_terms = get_terms('product_cat', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
    if (!$parent_terms || is_wp_error($parent_terms)){
      $parent_terms = array();
    }
    foreach ( $parent_terms as $pterm ) {
      $ctgs[] = $pterm;
    }

    wp_send_json($ctgs);
  }

  public function getTermsProductCategory(){
    $ctgs = array();
    $parent_terms = get_terms('product_cat', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
    foreach ( $parent_terms as $pterm ) {
      //Get the Child terms
      $terms = get_terms( 'product_cat', array( 'parent' => $pterm->term_id, 'orderby' => 'slug', 'hide_empty' => false ) );
      if (!$terms || is_wp_error($terms)){
        $terms = array();
      }
      $ctgs[] = $pterm;
      foreach ( $terms as $term ) {
          $ctgs[] = $term;
      }
    }
    wp_send_json($ctgs);
  }
}
