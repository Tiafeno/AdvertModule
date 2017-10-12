<?php
namespace advert\shortcode\adverts;
use advert\shortcode as shortcode;

final class AdvertsCode {

  public function __construct() { return; }

  /**
  * [adverts orderBy="%s" order="%s"]
  *
  * Get all adverts list content
  * This is a function shortcode, get all product post type
  *
  * @function RenderAdvertsLists
  * @param $attrs, $content
  * @return wp_send_json (json)
  **/
  public static function Render($attrs, $content = null) {
		$attributs = \shortcode_atts(array(
			'orderBy' => 'date',
			'order' => 'DESC'
		), $attrs);

    $user_id = null;
    if (\is_user_logged_in()) {
      /*
      * @function  wp_get_current_user
      * Will set the current user, if the current user is not set. 
      * The current user will be set to the logged-in person.
      * If no user is logged-in, then it will set the current user to 0 
      * @return WP_User
      */
      $current_user = \wp_get_current_user();
      $user_id = $current_user->ID;
		}
		
		/* 
		* @var $thumbnails
		* e.g [{'post_id': 154, 'thumbnail_url': '...'}] , 
		* PS: `post_id` is id post product type or not thumbnail post id
		*/
		$thumbnails = []; 
		$posts = [];
		$args = [
			'post_type' => 'product',
			'posts_per_page' => 20,
			'order' => $attributs[ 'order' ],
			'orderby' => $attributs[ 'orderBy' ]
		];
		$adverts = new \WP_Query( $args );
		if ($adverts->have_posts()) {
			while ($adverts->have_posts()) : $adverts->the_post();
				array_push($thumbnails, [
					'post_id' => $adverts->post->ID,
					'thumbnail_url' => \get_the_post_thumbnail_url( $adverts->post->ID,  'full' )
				]);
				array_push($posts, [
					'ID' => $adverts->post->ID,
					'post_title' => $adverts->post->post_title,
					'post_date' => $adverts->post->post_date,
					'post_excerpt' => $adverts->post->post_content,
					'price' => \get_post_meta( $adverts->post->ID, '_price', true),
					/* @return false or array (WP_Term) */
					'categorie' => \get_the_terms( $adverts->post->ID, 'product_cat' )[ 0 ],
					/* --- */
					'adress' => \get_post_meta( $adverts->post->ID, '_product_advert_adress', true),
					'state' => \get_post_meta( $adverts->post->ID, '_product_advert_state', true)
				]);
			endwhile;
		}
		
		if ($adverts->have_posts()) {
			global $twig;
			if (is_null( $twig )) {
				return 'Active or install Template Engine TWIG';
			}
			shortcode\AdvertCode::setEnqueue();
			shortcode\AdvertCode::setUIKit();

			$params = new \stdClass();
			$params->posts = &$posts;
			$params->thumbnails = &$thumbnails;
			shortcode\AdvertCode::AdvertsEnqueue( $params );
			
			/* create filter twig */
			$get_post_thumbnail = new \Twig_SimpleFilter('get_full_post_thumbnail', function( $id ) {
				return \get_the_post_thumbnail_url( $id, 'full' );
			});
			$twig->addFilter( $get_post_thumbnail );

			return $twig->render('@frontadvert/advert.html', array(
				'user_id' => $user_id
			));
		}
		\wp_reset_postdata();
		return 'No post!';
  }
}