<?php
namespace advert\shortcode\addform;
use advert\shortcode as shortcode;
use advert\shortcode\login as Login;

final class AddformCode {
  public function __construct() {}

  /*
	* [addform_advert user_id="%d"]
	*/
	public static function Render($attrs, $content) {
		global $twig;
		if (is_null( $twig )){
			return 'Active or install Template Engine TWIG';
		}
		shortcode\AdvertCode::setEnqueue();
		shortcode\AdvertCode::setAngularMaterial();
		
		if (!\is_user_logged_in()) {
			return Login\LoginCode::Render();
		}
		
		$current_user = \wp_get_current_user();
		$at = \shortcode_atts(array(
				'user_id' => $current_user->ID
			), $attrs);
		
		$args = [
			'post_type' => "product",
			'post_status' => [ 'pending' ],
			'post_author' => $current_user->user_login
		];
		$Pending = new \WP_Query( $args );
		$post_id = null;
		if ($Pending->have_posts()){
			while ($Pending->have_posts()): $Pending->the_post();
				$postid = $Pending->post->ID;
				$gallery_ids = \get_post_meta( $postid, '_product_image_gallery', true );
				$thumbnail_id = \get_post_meta( $postid, '_thumbnail_id', true );
				if (!empty($gallery_ids)) {
					$gallery = explode(',', $gallery_ids);
					while (list(, $id) = each( $gallery )) {
						\wp_delete_attachment( (int)$id, true );
					}
				}
				if (!empty( $thumbnail_id )) {
					\wp_delete_attachment( (int)$thumbnail_id, true );
				}
				
				\wp_delete_post( $postid, true );
				break;
			endwhile;
		} 
		\wp_reset_postdata();
		/*
		* $current_user->ID
		* $current_user->user_login
		* $current_user->user_email
		*/
		$post_id = wp_insert_post(array(
			'post_author' => $current_user->user_login,
			'post_title' => \wp_strip_all_tags(md5( date( 'Y-m-d H:i:s' ) ) . ' - ' . $current_user->user_login),
			'post_date' => date( 'Y-m-d H:i:s' ),
			'post_content' => '',
			'post_status' => 'pending', /* https://codex.wordpress.org/Post_Status */
			'post_parent' => '',
			'post_type' => "product",
		));

		\wp_reset_postdata();
		if (is_null( $post_id )) new \WP_Error('Warning', 'Variable post_id is null');
		$products_cat = [];
		$products_cat_child = [];
		$vendors = []; 
		$Services = new Services\ServicesController();
		$Schema = $Services->getSchemaAdvert();
		$AdvertSchema = json_decode( $Schema );

		/* Set Add form script and style */
		$params = new \stdClass();
		$params->post_id = $post_id;
		$params->vendors = $AdvertSchema->vendor;
		$params->products_cat = $AdvertSchema->product_cat_child;
		shortcode\AdvertCode::AddformEnqueue( $params );

		$login_link = \get_option( 'login_page_id', false ) ? \get_permalink(\get_option( 'login_page_id', false )) : '#login';
		return $twig->render('@frontadvert/addform.advert.html', array(
			'nonce' => \wp_nonce_field('thumbnail_upload', 'thumbnail_upload_nonce'),
			'post_id' => $post_id,
			'terms' => $content,
			'vendors' => $AdvertSchema->vendor
			
		));
	}
}