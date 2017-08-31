<?php
namespace advert\src\controller;
use advert\src\services as services;

abstract class AdvertController {
  public $Services;

  public function __construct(){
    $this->Services = new services\ServicesController();
  }

  public function getParentsTermsCat(){
    $ctgs = array();
    $parent_terms = \get_terms('product_cat', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
    if (!$parent_terms || \is_wp_error( $parent_terms )){
      $parent_terms = array();
    }
    foreach ( $parent_terms as $pterm ) {
      $ctgs[] = $pterm;
    }

    \wp_send_json($ctgs);
  }

  public function getTermsProductCategory(){
    $ctgs = array();
    $parent_terms = \get_terms('product_cat', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
    foreach ( $parent_terms as $pterm ) {
      //Get the Child terms
      $terms = \get_terms( 'product_cat', array( 'parent' => $pterm->term_id, 'orderby' => 'slug', 'hide_empty' => false ) );
      if (!$terms || \is_wp_error( $terms )){
        $terms = array();
      }
      $ctgs[] = $pterm;
      foreach ( $terms as $term ) {
          $ctgs[] = $term;
      }
    }
    \wp_send_json( $ctgs );
  }
}
