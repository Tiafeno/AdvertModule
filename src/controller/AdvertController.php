<?php
namespace advert\src\controller;
use advert\src\services as services;

abstract class AdvertController {
  public $Services;
  public $advert_error;

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

  public function controller_getProduct(){
    if (!isset( $_REQUEST['post_id'])) return;
    $post_id = (int) $_REQUEST[ 'post_id' ];
    $posts = \get_post( $post_id );
    $results = new \stdClass();
    if (!is_null($posts)){
      $results->ID = $posts->ID;
      $results->post_content = $posts->post_content;
      $results->post_title = $posts->post_title;

      $results->gallery = \get_post_meta( $posts->ID, '_product_image_gallery', true );
      $results->categorie = \get_the_terms( $posts->ID, 'product_cat'); // Array of WP_term or false
      \wp_send_json( $results );
    } else \wp_send_json([ 'type' => 'error', 'msg' => 'get post content is null']);

  }
}
