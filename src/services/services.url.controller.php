<?php
namespace advert\src\services\url;

final class ServiceUrlController {

  function __construct() {}

  public static function getAdvertDetailsUrl( $post_id ) {
    return \home_url('/') . "#!/advert/" . $post_id;
  }

  public static function getAttachmentUrl( $post_id, $size = 'full' ) {
    if (!is_int( $post_id ))
      return null;
    if ($size != 'full' && !is_array( $size )) return false;
    $url = \wp_get_attachment_image_src($post_id, $size)[0];
    return $url;

  }
}
