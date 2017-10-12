<?php
namespace advert\src\services\url;

final class ServiceUrlController {

  function __construct() {}
    
  public static function getAdvertDetailsUrl( $post_id ) {
    return \home_url('/') . "#!/advert/" . $post_id;
  }

  public static function getAttachmentUrl( $post_id ) {
    if (!is_int( $post_id ))
      return null;
    $url = \wp_get_attachment_image_src($post_id, 'full')[ 0 ];
    return $url;

  }
}