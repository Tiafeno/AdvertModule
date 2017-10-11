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

  public function verify_before_delete( $postid ) {
    global $post_type;
    if ('product' != $post_type) return;
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

  public function action_change_password() {
    if (!isset( $_REQUEST[ 'pass' ])) 
      \wp_send_json( [
        'type' => false,
        'data' => 'Probably, an request error params'
      ] );
    if (!\is_user_logged_in()) 
      return;
    // if (defined('DOING_AJAX') && DOING_AJAX)
    //   return false;
    $password = base64_decode( trim($_REQUEST[ 'pass' ]) );
    $User = \wp_get_current_user();
    /* @function wp_set_password( $password, $user_id ) */
    \wp_set_password( $password, $User->ID );
    \wp_send_json( [
      'type' => true,
      'data' => 'Password update successfuly!'
    ] );
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

  public function action_render_nonce() {
    if (isset( $_REQUEST[ 'fieldnonce' ])) {
      $fieldnonce = trim( $_REQUEST[ 'fieldnonce' ]);
      \wp_send_json( [
        'type' => true,
        'nonce' => \wp_create_nonce( $fieldnonce )
      ] );
    }
  }

  public function action_upload_avatar() {
    if (!\is_user_logged_in()) return false;
    if (isset($_REQUEST[ 'nonce' ]) && 
    \wp_verify_nonce($_REQUEST[ 'nonce' ], 'avatar_upload')) {
      $User = \wp_get_current_user();
      /* delete preview avatar */
      $prev_avatar = \get_user_meta( $User->ID, '_avatar_', true );
      if (!empty($prev_avatar)) {
        $results = \wp_delete_attachment( $prev_avatar );
      }
      /* add new avatar */
      require_once( ABSPATH . 'wp-admin/includes/image.php' );
      require_once( ABSPATH . 'wp-admin/includes/file.php' );
      require_once( ABSPATH . 'wp-admin/includes/media.php' );
      $attachment_id = \media_handle_upload('file', 0);
      if (\is_wp_error( $attachment_id )) {
        \wp_send_json(array(
          'data' => 'There was an error uploading the image.', 
          'tracking' => $attachment_id->get_error_messages(), 
          'type' => false)
        );
      } else {
        $user_avatar = \update_user_meta($User->ID, '_avatar_', $attachment_id);
        \wp_send_json(array(
          'data' => 'The image was uploaded successfully!',
          'attach_id' => $attachment_id,
          'update_avatar_results' => $user_avatar,
          'url' => \wp_get_attachment_image_src($attachment_id, array(250, 250))[ 0 ],
          'type' => true)
        );
      }
    }
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
