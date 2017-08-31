<?php
namespace advert\src\services;

class ServicesController {
  public function __construct(){}
  public function setThumbnailbyRequestPostId( $attachment_id, $post_id ) { // Action
    if (!\is_user_logged_in())
      return false;

    if (!is_int( $attachment_id )) return false;
    if (\update_post_meta($post_id, '_thumbnail_id', $attachment_id)) {
      \wp_send_json(array('msg' => 'Update post success', 'type' => 'success'));
    } else { \wp_send_json(array('msg' => 'Update post failure, function : setThumbnailbyRequestPostId', 'type' => 'error')); }
  }

  public function getSchemaAdvert(){
    return file_get_contents( \plugin_dir_path(__FILE__)."schema/advert.json");
  }

}