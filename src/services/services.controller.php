<?php
namespace advert\src\services;
use advert\entity\model as Model;

class ServicesController {
  public static $vendor = [];

  public function __construct(){}
    
  public function setThumbnail( $attachment_id, $post_id ) { // Action
    if (!\is_user_logged_in())
      return false;

    $_attachment_id = \get_post_meta( $post_id, '_thumbnail_id', true);
    if (!is_int( $attachment_id )) return;
    if ((int)$_attachment_id === $attachment_id ) \wp_send_json( [
      'type' => true,
      'data' => 'Already exist',
      'status' => $_attachment_id
    ] );
    $updateStatus = \update_post_meta($post_id, '_thumbnail_id', $attachment_id);
    if ( (true == $updateStatus) || is_int( $updateStatus ) ) {
      \wp_send_json(array('data' => 'Update post success', 'type' => true, 'status' => $updateStatus));
    } else { 
      \wp_send_json(array(
        'data' => 'Update post failure', 
        'info' => [ 'postid' => $post_id, 'attachmentid' => $attachment_id],
        'tracking' => 'Service Controller Error: Update post meta thumbnail on services', 
        'error' => $updateStatus,
        'type' => false
        )
      ); 
    }
  }

  public static function getAttachmentUrl( $post_id ) {
    if (!is_int( $post_id ))
      return null;
    $url = \wp_get_attachment_image_src($post_id, 'full')[ 0 ];
    return $url;

  }

  public static function getPost( $post_id ) {
    if (!function_exists('wc_get_product')) return false;
    $_product = \wc_get_product( (int)$post_id );
    if (!is_null($_product)) {
      $urlsGallery = [];
      $gallery = $_product->get_gallery_image_ids();
      array_push( $gallery, $_product->get_image_id() );
      while (list(, $id) = each( $gallery )) {
        $urlsGallery[] = self::getAttachmentUrl( (int)$id );
      }

      $results = new \stdClass();
      $results->ID = (int)$_product->get_id();
      $results->post_content = $_product->get_description();
      $results->post_excerpt = $_product->get_short_description();
      $results->post_title = $_product->get_title();
      $results->categorie = \get_the_terms( $_product->get_id(), 'product_cat' ); // Array of WP_term or false
      $results->state = \get_post_meta( $_product->get_id(), '_product_advert_state', true );
      $results->adress = \get_post_meta( $_product->get_id(), '_product_advert_adress', true );
      $results->phone = \get_post_meta( $_product->get_id(), '_product_advert_phone', true ); 
      $results->hidephone = \get_post_meta( $_product->get_id(), '_product_advert_hidephone', true );
      $results->pictures = &$urlsGallery;
      $results->price = $_product->get_price();

      $attrs = $_product->has_attributes() ? $_product->get_attributes() : null;
      $_ = [];
      if (!is_null($attrs)) {
        foreach ($attrs as $validate => $attribute) {
          array_push($_, [ 'attr' => self::getAttributName( $validate ), 'value' => $attribute->get_options() ]);
        }
      }
      return [ 
        'post' => $results,
        'attributs' => $_
      ];
    } else {
      return null;
    }
  }

  public static function getUser( $user_id ) {
    $User = \get_user_by('ID', $user_id);
    $Model = new Model\AdvertModel();

    /* @function get_advert_user
    *  @params $user_id (int)
    *  @return wordpress database results where user_id = $user_id from advert_user
    */
    $advertUser = $Model->get_advert_user( $user_id ) [0];
    $advertUser->user_login = $User->user_login;
    $advertUser->user_email = $User->user_email;
    $advertUser->user_registered = $User->user_registered;
    $advertUser->user_nicename = $User->user_nicename;
    $advertUser->display_name = $User->display_name;
    $advertUser->token = $User->user_pass;

    /* Get user avatar */
    $user_avatar = \get_user_meta( $user_id, '_avatar_', true );
    if (!empty($user_avatar))
      $advertUser->img_url = \wp_get_attachment_image_src((int)$user_avatar, array(250, 250))[ 0 ];

    return $advertUser;
  }

  public static function getVendor() {
    $Services = new ServicesController();
    $Schema = $Services->getSchemaAdvert();
    $Schema = json_decode( $Schema );
    return $Schema->vendor;
  }

  private static function getAttributName( $validate ) {
    $vendor_id = null;
    self::$vendor = self::getVendor();
    while (list(, $vendor) = each( self::$vendor )) {
      if ($vendor->validate != trim( $validate )) continue;
      $vendor_id = $vendor->id;
      break;
    }
    return $vendor_id; // translate
  }

  public function getSchemaAdvert(){
    return file_get_contents( \plugin_dir_path(__FILE__)."schema/advert.json");
  }

  public function getAdvertDetailsUrl( $post_id ) {
    return \home_url('/') . "#!/advert/" . $post_id;
  }

}