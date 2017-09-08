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

  public function getSchemaAdvert(){
    return file_get_contents( \plugin_dir_path(__FILE__)."schema/advert.json");
  }

}