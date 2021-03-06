<?php
namespace advert\src\services;
use advert\entity\model as Model;
use advert\src\services\url as UrlServices;
use advert\libraries\php\underscore\__ as __;

final class ServicesController {
  public static $vendor = [];

  public function __construct(){}

  public function setThumbnail( $attachment_id, $post_id ) { // Action
    if (!\is_user_logged_in())
      return false;

    $_attachment_id = \get_post_meta( $post_id, '_thumbnail_id', true);
    if ((int)$_attachment_id === $attachment_id ) 
      \wp_send_json( [
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

  public static function getAuthor( $post_id ) {
    $post = \get_post( (int)$post_id );
    if (is_null( $post )) return false;
    $User = \get_user_by('ID', (int)$post->post_author );
    if (false === $User) return $User;
    return [
      'mail' => $User->user_email,
      'name' => $User->display_name,
      'post_title' => $post->post_title
    ];
  }

  public static function getPost( $post_id ) {
    if (!function_exists('wc_get_product')) return false;
    $_product = \wc_get_product( (int)$post_id );
    if (!is_null($_product)) {
      $urlsGallery = [];
      $gallery = $_product->get_gallery_image_ids();
      array_push( $gallery, $_product->get_image_id() );
      while (list(, $id) = each( $gallery )) {
        $urlsGallery[] = [
          'full' => UrlServices\ServiceUrlController::getAttachmentUrl( (int)$id ),
          'thumbnail' => UrlServices\ServiceUrlController::getAttachmentUrl( (int)$id , [100, 100])
        ];
      }

      $results = new \stdClass();
      $results->ID = (int)$_product->get_id();
      $results->post_content = $_product->get_description() ;
      $results->post_excerpt = $_product->get_short_description();
      $results->post_title = $_product->get_title();
      $results->categorie = \get_the_terms( $_product->get_id(), 'product_cat' ); // Array of WP_term or false
      $results->state = \get_post_meta( $_product->get_id(), '_product_advert_state', true );
      $results->adress = \get_post_meta( $_product->get_id(), '_product_advert_adress', true );
      $results->phone = \get_post_meta( $_product->get_id(), '_product_advert_phone', true );
      $results->hidephone = \get_post_meta( $_product->get_id(), '_product_advert_hidephone', true );
      $results->pictures = &$urlsGallery;
      $results->price = $_product->get_price();
      $results->date_create = $_product->get_date_created();

      $post = \get_post( $results->ID );
      $Model = new Model\AdvertModel();
      $advertUser = $Model->get_advert_user( (int)$post->post_author );
      $results->author_name = $advertUser->lastname.' '.$advertUser->firstname;
      $results->post_author = $post->post_author;

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

  public static function delete_post_attachment( $post_id ) {
    $errors = [];
    if ( ! is_int( $post_id )) $errors[] = 'Variable argument post_id is not int type value';
    $attachment_id = \get_post_meta( $post_id, '_thumbnail_id', true);
    if ( ! empty( $attachment_id ) && empty( $errors )) {
      $id = (int) $attachment_id;
      if ( ! is_int( $id )) array_push($errors, 'Error of attachment id: ' . $id);
      if (empty( $errors )) :
        $delete_results = \wp_delete_attachment( $id, true );
      endif;
    } else return $errors;
    $gallery_ids = \get_post_meta( $post_id, '_product_image_gallery', true);
    if ( ! empty( $gallery_ids )) {
      $ids = explode(',', $gallery_ids);
      if ( ! is_array( $ids )) array_push($errors, 'gallery is not array variable');
      __.each($ids, function( $value ) {
        $delete_results = \wp_delete_attachment( $value, true );
        if (false === $delete_results) {
          array_push($errors, 'Error on delete attachment id: ' . $value);
        }
      });
    }

    return $errors;

  }

  private static function getShopAvatar( $user_id ) {
    /* Get user avatar */
    $user_avatar = \get_user_meta( $user_id, '_avatar_', true );
    if (!empty($user_avatar))
      return \wp_get_attachment_image_src((int)$user_avatar, array(250, 250))[ 0 ];
    return '';
  }

  public static function getUser( $user_id ) {
    $User = \get_user_by('ID', $user_id);
    $Model = new Model\AdvertModel();

    /* @function get_advert_user
    *  @params $user_id (int)
    *  @return wordpress database results where user_id = $user_id from advert_user
    */
    $advertUser = $Model->get_advert_user( $user_id );
    if (is_null( $advertUser) ) { 
      $advertUser = new \stdClass();
      $advertUser->firstname = $User->user_login; 
      $_REQUEST[ 'user_id' ] = $user_id;
      $_REQUEST[ 'firstname' ] = $advertUser->firstname;
      $Model->add_user( $user_id );
    }
    $advertUser->user_login = $User->user_login;
    $advertUser->user_email = $User->user_email;
    $advertUser->user_registered = $User->user_registered;
    $advertUser->user_nicename = $User->user_nicename;
    $advertUser->display_name = $User->display_name;
    $advertUser->token = $User->user_pass;

    /* Get user avatar */
    $advertUser->img_url = self::getShopAvatar( $user_id );

    return $advertUser;
  }

  public static function getShops() {
    $Shops = [];
    $Model = new Model\AdvertModel();
    $Users = $Model->get_users();
    if (is_null( $Users )) return null;
    while(list(, $User) = each( $Users)) {
      $current_user = \get_user_by('ID', (int)$User->id_user);
      $shop = new \stdClass();
      $shop->id_user = (int)$User->id_user;
      $shop->society = $User->society;
      $shop->adress = $User->adress;
      $shop->postal_code = $User->postal_code;
      $shop->img_url = self::getShopAvatar( (int)$User->id_user);

      array_push($Shops, $shop);
    }
    return $Shops;
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

  public function getSchemaAdvert() {
    return file_get_contents( \plugin_dir_path(__FILE__)."schema/advert.json");
  }

  public function getSChemaDistricts() {
    return file_get_contents( \plugin_dir_path(__FILE__)."schema/districts.json");
  }


}
