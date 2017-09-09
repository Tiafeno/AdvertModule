<?php
namespace advert\src\services;

class ServicesController {
  public function __construct(){}
    
  public function setThumbnail( $attachment_id, $post_id ) { // Action
    if (!\is_user_logged_in())
      return false;

    if (!is_int( $attachment_id )) return false;
    $updateStatus = \update_post_meta($post_id, '_thumbnail_id', $attachment_id);
    if ( $updateStatus ) {
      \wp_send_json(array('data' => 'Update post success', 'type' => true, 'status' => $updateStatus));
    } else { 
      \wp_send_json(array(
        'data' => 'Update post failure', 
        'tracking' => 'Service Controller Error: Update post meta thumbnail on services', 
        'error' => $updateStatus,
        'type' => false
        )
      ); 
    }
  }

  public static function getPost( $post_id ) {
    $posts = \get_post( (int)$post_id );
    if (!is_null($posts)) {
      $results = new \stdClass();
      $results->ID = (int)$post_id;
      $results->post_content = $posts->post_content;
      $results->post_title = $posts->post_title;

      $results->gallery = \get_post_meta( $posts->ID, '_product_image_gallery', true );
      $results->categorie = \get_the_terms( $posts->ID, 'product_cat'); // Array of WP_term or false

      $results->state = \get_post_meta( $posts->ID, '_product_advert_state', true );
      $results->adress = \get_post_meta( $posts->ID, '_product_advert_adress', true );
      $results->phone = \get_post_meta( $posts->ID, '_product_advert_phone', true); 
      $results->hidephone = \get_post_meta( $posts->ID, '_product_advert_hidephone', true );
      $results->gallery = \get_post_meta( $posts->ID, '_product_image_gallery', true);
      $results->thumbnail = \get_post_meta( $posts->ID, '_thumbnail_id', true);
      $results->price = \get_post_meta( $posts->ID, '_price', true);

      return $results;
    } else {
      return null;
    }
  }

  public function getSchemaAdvert(){
    return file_get_contents( \plugin_dir_path(__FILE__)."schema/advert.json");
  }

}