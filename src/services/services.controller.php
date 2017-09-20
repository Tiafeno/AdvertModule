<?php
namespace advert\src\services;

class ServicesController {
  public static $vendor = [];

  public function __construct(){}
    
  public function setThumbnail( $attachment_id, $post_id ) { // Action
    if (!\is_user_logged_in())
      return false;

    $_attachment_id = \get_post_meta( $post_id, '_thumbnail_id', true);
    if (!is_int( $attachment_id )) return;
    if ((int)$_attachment_id === $attachment_id ) return;
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

  public static function getPost( $post_id ) {
    if (!function_exists('wc_get_product')) return false;
    $_product = \wc_get_product( (int)$post_id );
    if (!is_null($_product)) {
      $results = new \stdClass();
      $results->ID = (int)$_product->get_id();
      $results->post_content = $_product->get_description();
      $results->post_excerpt = $_product->get_short_description();
      $results->post_title = $_product->get_title();

      $results->gallery = $_product->get_gallery_image_ids();
      $results->categorie = \get_the_terms( $_product->get_id(), 'product_cat'); // Array of WP_term or false

      $results->state = \get_post_meta( $_product->get_id(), '_product_advert_state', true );
      $results->adress = \get_post_meta( $_product->get_id(), '_product_advert_adress', true );
      $results->phone = \get_post_meta( $_product->get_id(), '_product_advert_phone', true); 
      $results->hidephone = \get_post_meta( $_product->get_id(), '_product_advert_hidephone', true );
      $results->gallery = \get_post_meta( $_product->get_id(), '_product_image_gallery', true);
      $results->thumbnail = $_product->get_image_id();
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

}