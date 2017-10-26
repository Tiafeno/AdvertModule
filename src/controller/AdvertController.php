<?php
namespace advert\src\controller;
use advert\src\services as services;
use advert\src\factory as factory;

abstract class AdvertController {
  public $Services;
  public $advert_error;

  public function __construct() {
    /* Create new services instance */
    $this->Services = new services\ServicesController();
  }

  /*
  ** action 'before_delete_post'
  ** This function check if post type is only product
  */
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
    $fieldnonce = services\Request::req('fieldnonce');
    if (false != $fieldnonce) {
      \wp_send_json( [
        'nonce' => \wp_create_nonce( $fieldnonce )
      ] );
    }
  }

  public function action_get_nonce( $paramNonce = false ) {
    $inputNonce = services\Request::req( 'inputNonce', $paramNonce );
    if (false != $inputNonce) {
      $factory = new factory\Factory( $inputNonce );
      return $factory->getNonce();
    }
  }

  public function action_delete_product() {
    if ( ! \wp_doing_ajax()) return;
    if ( ! \is_user_logged_in()) return;
    $User = \wp_get_current_user();
    $post_id = (int) services\Request::req( 'post_id' );
    $pt = \get_post( $post_id );
    if (is_null( $pt )) \wp_send_json( 'Post doesn\'t exist or unknown error' );
    if ( ! $pt instanceof \WP_Post) \wp_send_json( 'Current post is not instance of WP_POST' );
    if ($pt->post_author != $User->ID) \wp_send_json( ['info' => 'This post isn\'t your post' ] );

    /* delete post attachment */
    $delete_attachment = services\ServicesController::delete_post_attachment( $post_id ); // @return array of error
    if (is_array( $delete_attachment) && !empty( $delete_attachment )) 
      \wp_send_json( ['data' => $delete_attachment,  'type' => false] );
    /* delete post */
    $deletion = \wp_delete_post( $post_id, true);
    if (false != $deletion) {
      \wp_send_json( ['type' => true, 'post' => $deletion, 'attachment' => $delete_attachment] );
      
    } else \wp_send_json( ['type' => false, 'data' => 'Error unknown on delete the post']);
  }

  public function action_update_product() {
    if ( ! \wp_doing_ajax()) return;
    if ( ! \is_user_logged_in()) return;
    $User = \wp_get_current_user();
    $post_id = (int) services\Request::req( 'post_id' );
    $formNonce = services\Request::req( 'inputNonce' );
    if ($formNonce === false) return false;
    $factory = new factory\Factory( _update_product_nonce_ );
    if ($resultNonce = $factory->verifyNonce( $formNonce )) {
      $title = services\Request::req( 'inputTitle' );
      $content = services\Request::req( 'inputContent' );
      $state = services\Request::req( 'inputState' );
      $adress = services\Request::req( 'inputAdress' );
      $phone = services\Request::req( 'inputPhone' );
      if (false == $post_id) \wp_send_json( [ 'constant post_id isn\'t define or empty.' ] );
      /* Check if current user can edit */
      $pst = \get_post( $post_id );
      if (is_null( $pst )) \wp_send_json( 'Post doesn\'t exist or error' );
      if ( ! $pst instanceof \WP_Post) \wp_send_json( 'Current post is not instance of WP_POST' );
      if ($pst->post_author != $User->ID) \wp_send_json( ['info' => 'This post isn\'t your post' ] );
      if ($title != false && $content != false) {
        $post = [
          'ID'     => $post_id,
          'post_title'   =>  $title ,
          'post_content' =>  $content ,
        ];
    
        /* Update the post -*/
        $update_result = \wp_update_post( $post, true );
        if (!\is_wp_error( $update_result )) {
          \update_post_meta( $post_id, '_product_advert_state', $state );
          \update_post_meta( $post_id, '_product_advert_adress', $adress );
          \update_post_meta( $post_id, '_product_advert_phone', $phone );

          \wp_send_json( [ 'type' => true, 'data' => 'Product update with success'] );
        } else 
          \wp_send_json( [ $update_result->get_error_messages() ] );
      } else \wp_send_json( $title, $content );

    } else \wp_send_json( $resultNonce );

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
