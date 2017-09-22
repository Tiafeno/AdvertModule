<?php
namespace advert\src\controller;
use advert\src\services as services;

abstract class AdvertController {
  public $Services;
  public $advert_error;

  public function __construct() {
    /* Create new services instance */
    $this->Services = new services\ServicesController();
  }

  public function getParentsTermsCat() {
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

  public function getTermsProductCategory() {
    $ctgs = array();
    $parent_terms = \get_terms('product_cat', array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) );
    foreach ( $parent_terms as $pterm ) {
      //Get the Child terms
      $terms = \get_terms( 'product_cat', array( 'parent' => $pterm->term_id, 'orderby' => 'slug', 'hide_empty' => false ) );
      if (!$terms || \is_wp_error( $terms )) {
        $terms = array();
      }
      $ctgs[] = $pterm;
      foreach ( $terms as $term ) {
          $ctgs[] = $term;
      }
    }
    \wp_send_json( $ctgs );
  }

  public function action_verify_password() {
    if (!isset( $_REQUEST[ 'pass' ])) return;
    if (!\is_user_logged_in()) return;
    $current_user = \wp_get_current_user();
    $User = \get_user_by('ID', $current_user->ID);

    $password = trim($_REQUEST[ 'pass' ]);
    \wp_send_json( [
      'type' => true,
      'token' => \wp_check_password( base64_decode($password), $User->user_pass)
    ] );
  }

  public function action_get_advertdetails() {
    if (!isset( $_REQUEST[ 'post_id' ])) return;
    $post_id = (int) $_REQUEST[ 'post_id' ];
    $posts = services\ServicesController::getPost( $post_id );

    if (!is_null($posts)) {
      \wp_send_json( [ 'type' => true, 'data' => $posts ]);
    } else \wp_send_json([ 'type' => false, 'tracking' =>  null, 'data' => 'get post content is null']);
  }

  public function action_get_vendors() {
    $vendors = services\ServicesController::getVendor();
    \wp_send_json( $vendors );
  }

}
