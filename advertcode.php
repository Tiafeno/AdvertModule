<?php
namespace advert\plugins\shortcode;
use advert\src\services as Services;

class AdvertCode {
	
	public function __construct() {
		return;
	}
	
	private static function setEnqueue(){
		\wp_enqueue_style('material-style', \plugins_url('/assets/components/angular-material/angular-material.css', __FILE__), array());
		
		\wp_enqueue_script( 'underscore', \plugins_url('/libraries/underscore/underscore.js', __FILE__));
		\wp_enqueue_script('angular', \plugins_url('/assets/components/angular/angular.js', __FILE__), array('jquery'));
		\wp_enqueue_script('aria', \plugins_url('/assets/components/angular-aria/angular-aria.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-messages', \plugins_url('/assets/components/angular-messages/angular-messages.js', __FILE__), array());
		\wp_enqueue_script('angular-animate', \plugins_url('/assets/components/angular-animate/angular-animate.js', __FILE__), array());
		\wp_enqueue_script('angular-sanitize', \plugins_url('/assets/components/angular-sanitize/angular-sanitize.js', __FILE__), array());
		\wp_enqueue_script('material', \plugins_url('/assets/components/angular-material/angular-material.js', __FILE__), array('angular'));
		
	}
	
	public static function getLoginForm(){
		global $twig;
		$args = array();
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
		
		\wp_enqueue_script('LoginAdvertCtrl', \plugins_url('/assets/js/login.advert.js', __FILE__), array('angular'));
		\wp_localize_script('AdvertCtrl', 'advert', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__)
		));

		
		return $twig->render('@frontadvert/loginform.advert.html', array(
			'form_id' => $args['form_id'],
			'action' => \esc_url( \site_url( 'wp-login.php', 'login_post' ) ),
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
			
			'register_link' => \get_permalink(\get_option( 'register_page_id' ))
			
			
		));
	}

	/**
  * [adverts orderBy="%s" order="%s"]
  *
  * Get all adverts list content
  * This is a function shortcode, get all product post type
  *
  * @function get_adverts
  * @param $attrs, $content
  * @return wp_send_json (json)
  **/
  public static function get_adverts($attrs, $content = null) {
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
		namespace\AdvertCode::setEnqueue();

		/* 
		* e.g [{'post_id': 154, 'thumbnail_url': '...'}] , 
		* PS: `post_id` is id post product type or not thumbnail post id
		*/
		$thumbnails = []; 
		$args = [
			'post_type' => 'product',
			'posts_per_page' => 20,
			'order' => $attributs[ 'order' ],
			'orderby' => $attributs[ 'orderBy' ]
		];
		$adverts = new \WP_Query( $args );
		if ($adverts->have_posts()){
			while($adverts->have_posts()): $adverts->the_post();
				array_push($thumbnails, [
					'post_id' => $adverts->post->ID,
					'thumbnail_url' => \get_the_post_thumbnail_url( $adverts->post->ID, 'full' )
				]);
			endwhile;
		}
		
		if ($adverts->have_posts()) {
			global $twig;
			if (is_null( $twig )){
				return 'Active or install Template Engine TWIG';
			}
			\wp_enqueue_script( 'advert', \plugins_url('/assets/js/advert.js', __FILE__), ['angular'] );
			\wp_enqueue_script( 'advert-filter', \plugins_url('/assets/js/advert.filter.js', __FILE__), ['advert'] );
			\wp_enqueue_script( 'advert-controller', \plugins_url('/assets/js/advert.controller.js', __FILE__), ['advert'] );
			\wp_localize_script( 'advert-controller', 'adverts', [
				'thumbnails' => $thumbnails
			] );
			/* create filter twig */
			$get_post_thumbnail = new \Twig_SimpleFilter('get_full_post_thumbnail', function( $id ) {
				return \get_the_post_thumbnail_url( $id, 'full' );
			});
			$twig->addFilter( $get_post_thumbnail );

			return $twig->render('@frontadvert/advert.html', array(
				'posts' => $adverts->posts,
				'user_id' => $user_id
			));
		}
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
		\wp_enqueue_script('Register', \plugins_url('/assets/js/register.js', __FILE__), array('angular'));
		\wp_enqueue_script('RegisterFactory', \plugins_url('/assets/js/register.factory.js', __FILE__), array('angular'));
		\wp_enqueue_script('AdvertRegisterCtrl', \plugins_url('/assets/js/register.advert.js', __FILE__), array('angular'));
		\wp_localize_script('AdvertRegisterCtrl', 'advert', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__)
		));
		return $twig->render('@frontadvert/registerform.advert.html', array(
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
		
		if (!\is_user_logged_in()) {
			return self::getLoginForm();
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
				$post_id = $Pending->post->ID;
				break;
			endwhile;
		} else {
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
		}
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
		\wp_enqueue_script( 'AddFormapp', \plugins_url('/assets/js/addform.js', __FILE__), array( 'angular' ));
		\wp_enqueue_script( 'addform-directive', \plugins_url('/assets/js/addform.directive.js', __FILE__), ['AddFormapp'] );
		\wp_enqueue_script( 'addform-factory', \plugins_url('/assets/js/addform.factory.js', __FILE__), ['AddFormapp'] );
		\wp_enqueue_script( 'addform-controller', \plugins_url('/assets/js/addform.controller.js', __FILE__), ['AddFormapp'] );
		\wp_localize_script( 'AddFormapp', 'advert', array(
			'ajax_url' => \admin_url( 'admin-ajax.php' ),
			'post_id' => $post_id,
			'vendors' => $AdvertSchema->vendor,
			'products_cat_child' => $AdvertSchema->product_cat_child,
			'assets_plugins_url' => \plugins_url( '/assets/', __FILE__ )
		));
		return $twig->render('@frontadvert/addform.advert.html', array(
			'nonce' => \wp_nonce_field('thumbnail_upload', 'thumbnail_upload_nonce'),
			'post_id' => $post_id,
			'terms' => $content,
			'vendors' => $AdvertSchema->vendor
		));
	}

}
