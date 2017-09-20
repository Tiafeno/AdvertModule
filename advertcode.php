<?php
namespace advert\plugins\shortcode;
use advert\src\services as Services;

class AdvertCode {
	
	public function __construct() {
		return;
	}
	
	private static function setEnqueue(){
		\wp_enqueue_style( 'dashicons' );
		\wp_enqueue_style( 'custom-style', \plugins_url('/assets/css/custom.css', __FILE__), []);
		\wp_enqueue_script('underscore', \plugins_url('/libraries/underscore/underscore.js', __FILE__));
		\wp_enqueue_script('angular', \plugins_url('/assets/components/angular/angular.js', __FILE__), array('jquery'));
		\wp_enqueue_script('aria', \plugins_url('/assets/components/angular-aria/angular-aria.min.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-messages', \plugins_url('/assets/components/angular-messages/angular-messages.min.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-animate', \plugins_url('/assets/components/angular-animate/angular-animate.min.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-sanitize', \plugins_url('/assets/components/angular-sanitize/angular-sanitize.js', __FILE__), array('angular'));
		
	}

	public static function setAngularMaterial() {
		\wp_enqueue_script('material', \plugins_url('/assets/components/angular-material/angular-material.min.js', __FILE__), array('angular'));
		\wp_enqueue_style('material-style', \plugins_url('/assets/components/angular-material/angular-material.min.css', __FILE__));
	}

	public static function setUIKit() {
		\wp_enqueue_style('uikit-style', \plugins_url('/assets/components/uikit/css/uikit.min.css', __FILE__), []);
		\wp_enqueue_script('uikit', \plugins_url('/assets/components/uikit/js/uikit.min.js', __FILE__), ['jquery']);
		\wp_enqueue_script('uikit-icon', \plugins_url('/assets/components/uikit/js/uikit-icon.min.js', __FILE__), array('uikit-style'));
	}

	/**
	* [myaccount_advert]
	**/
	public static function RenderMyAccount() {
		global $twig;

	}

	/**
	* [login_advert]
	**/

	public static function RenderLoginForm(){
		global $twig, $login_fail;

		$args = [];
		$defaults = array(
			'echo' => true,
			// Default 'redirect' value takes the user back to the request URI.
			'redirect' => \get_permalink(),
			'form_id' => 'loginform',
			'label_username' => __( 'Email Address' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in' => __( 'Log In' ),
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_submit' => 'wp-submit',
			'remember' => true,
			'value_username' => '',
			// Set 'value_remember' to true to default the "Remember me" checkbox to checked.
			'value_remember' => false,
		);
		$args = \wp_parse_args( $args, \apply_filters( 'login_form_defaults', $defaults ) );
		$login_form_top = \apply_filters( 'login_form_top', '', $args );
		$login_form_middle = \apply_filters( 'login_form_middle', '', $args );
		$login_form_bottom = \apply_filters( 'login_form_bottom', '', $args );
		
		namespace\AdvertCode::setEnqueue();
		namespace\AdvertCode::setUIKit();
		namespace\AdvertCode::setAngularMaterial();
		\wp_enqueue_script('LoginAdvertCtrl', \plugins_url('/assets/js/login/login.advert.js', __FILE__), array('angular'));
		\wp_localize_script('AdvertCtrl', 'advert', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__)
		));

		$register_link = \get_option( 'register_page_id', false ) ? \get_permalink(\get_option( 'register_page_id', false )) : '#register';
		return $twig->render('@frontadvert/loginform.advert.html', array(
			'login_fail' => $login_fail,
			'form_id' => $args['form_id'],
			'action' => \esc_url( site_url( 'wp-login.php', 'login_post' ) ), //$_SERVER[ 'REQUEST_URI' ]
			'login_form_top' => $login_form_top,
			'login_form_middle' => $login_form_middle,
			'login_form_bottom' => $login_form_bottom,
			
			'id_username' => \esc_attr($args['id_username']),
			'value_username' => \esc_attr($args['value_username']),
			'label_username' => \esc_html( $args['label_username'] ),
			
			'id_password' => \esc_attr($args['id_password']),
			'label_password' => \esc_html($args['label_password']),
			
			'remember' => $args['remember'],
			'id_remember' => \esc_attr( $args['id_remember'] ),
			'value_remember' => $args['value_remember'],
			'label_remember' => \esc_html( $args['label_remember'] ),
			
			'id_submit' => \esc_attr($args['id_submit']),
			'label_log_in' => \esc_attr( $args['label_log_in'] ),
			'redirect' => \esc_url( $args['redirect'] ),
			
			'register_link' => $register_link
			
		));
	}

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
  public static function RenderAdvertsLists($attrs, $content = null) {
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
			namespace\AdvertCode::setEnqueue();
			namespace\AdvertCode::setUIKit();

			\wp_enqueue_script( 'angular-route', \plugins_url('/assets/components/angular-route/angular-route.min.js', __FILE__), ['angular'] );
			\wp_enqueue_script( 'advert', \plugins_url('/assets/js/advert/advert.js', __FILE__), ['angular', 'angular-route', 'underscore'] );
			\wp_enqueue_script( 'advert-filter', \plugins_url('/assets/js/advert/advert.filter.js', __FILE__), ['advert'] );
			\wp_enqueue_script( 'advert-factory', \plugins_url('/assets/js/advert/advert.factory.js', __FILE__), ['advert'] );
			\wp_enqueue_script( 'advert-route', \plugins_url('/assets/js/route/advert.route.js', __FILE__), ['advert'] );
			\wp_localize_script( 'advert-route', 'jsRoute', [
				'partials_uri' => \plugins_url( '/assets/js/route/partials/', __FILE__ ),
				'ajax_url' => \admin_url( 'admin-ajax.php' )
			] );

			\wp_enqueue_script( 'moment', \plugins_url('/assets/components/moment/moment-with-locales.min.js', __FILE__), ['advert', 'advert-route'] );
			\wp_enqueue_script( 'advert-controller', \plugins_url('/assets/js/advert/advert.controller.js', __FILE__), ['advert', 'advert-route'] );
			\wp_localize_script( 'advert-controller', 'adverts', [
				'thumbnails' => $thumbnails,
				'posts' => $posts
			] );
			
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
	
	/*
	* [singin_advert]
	*/
	public static function RenderRegisterForm($attrs, $content){
		global $twig;
		if (is_null( $twig )){
			print 'Active or install Template Engine TWIG';
			return;
		}
		if (\is_user_logged_in())
			return self::RenderAddForm([], null);

		namespace\AdvertCode::setEnqueue();
		namespace\AdvertCode::setAngularMaterial();

		\wp_enqueue_script('Register', \plugins_url('/assets/js/register/register.js', __FILE__), array('angular'));
		\wp_enqueue_script('RegisterFactory', \plugins_url('/assets/js/register/register.factory.js', __FILE__), array('angular'));
		\wp_enqueue_script('AdvertRegisterCtrl', \plugins_url('/assets/js/register/register.controller.js', __FILE__), array('angular'));
		\wp_localize_script('AdvertRegisterCtrl', 'advert', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__)
		));
		$login_link = \get_option( 'login_page_id', false ) ? \get_permalink(\get_option( 'login_page_id', false )) : '#login';
		return $twig->render('@frontadvert/registerform.advert.html', array(
			'login_link' => $login_link
		));
		
		
	}
	
	/*
	* [addform_advert user_id="%d"]
	*/
	public static function RenderAddForm($attrs, $content) {
		global $twig;
		if (is_null( $twig )){
			return 'Active or install Template Engine TWIG';
		}
		namespace\AdvertCode::setEnqueue();
		namespace\AdvertCode::setAngularMaterial();
		
		if (!\is_user_logged_in()) {
			return self::RenderLoginForm();
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

		\wp_enqueue_style( 'advert', \plugins_url('/assets/css/advert.css', __FILE__), array());
		\wp_enqueue_style( 'air-datepicker', \plugins_url('/libraries/node_modules/air-datepicker/dist/css/datepicker.css', __FILE__), array('advert'));

		\wp_enqueue_script( 'air-datepicker', \plugins_url('/libraries/node_modules/air-datepicker/dist/js/datepicker.min.js', __FILE__), [ 'jquery' ]);
		\wp_enqueue_script( 'datepicker-lang-fr', \plugins_url('/libraries/node_modules/air-datepicker/dist/js/i18n/datepicker.fr.js', __FILE__), [ 'air-datepicker' ]);
		\wp_enqueue_script( 'AddFormapp', \plugins_url('/assets/js/addform/addform.js', __FILE__), array( 'angular' ));
		\wp_enqueue_script( 'addform-directive', \plugins_url('/assets/js/addform/addform.directive.js', __FILE__), ['AddFormapp'] );
		\wp_enqueue_script( 'addform-factory', \plugins_url('/assets/js/addform/addform.factory.js', __FILE__), ['AddFormapp'] );
		\wp_enqueue_script( 'addform-controller', \plugins_url('/assets/js/addform/addform.controller.js', __FILE__), ['AddFormapp', 'addform-directive', 'addform-factory'] );
		\wp_localize_script( 'AddFormapp', 'advert', array(
			'ajax_url' => \admin_url( 'admin-ajax.php' ),
			'post_id' => $post_id,
			'vendors' => $AdvertSchema->vendor,
			'products_cat_child' => $AdvertSchema->product_cat_child,
			'assets_plugins_url' => \plugins_url( '/assets/', __FILE__ )
		));
		$login_link = \get_option( 'login_page_id', false ) ? \get_permalink(\get_option( 'login_page_id', false )) : '#login';
		return $twig->render('@frontadvert/addform.advert.html', array(
			'nonce' => \wp_nonce_field('thumbnail_upload', 'thumbnail_upload_nonce'),
			'post_id' => $post_id,
			'terms' => $content,
			'vendors' => $AdvertSchema->vendor
			
		));
	}

}
