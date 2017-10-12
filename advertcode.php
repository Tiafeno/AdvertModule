<?php
namespace advert\shortcode;
use advert\src\services as Services;

/* @import shortcode class */
include_once(  \plugin_dir_path( __FILE__ ) . '/src/shortcode/shortcode.class.addform.php' );
include_once(  \plugin_dir_path( __FILE__ ) . '/src/shortcode/shortcode.class.adverts.php' );
include_once(  \plugin_dir_path( __FILE__ ) . '/src/shortcode/shortcode.class.dashboard.php' );
include_once(  \plugin_dir_path( __FILE__ ) . '/src/shortcode/shortcode.class.login.php' );
include_once(  \plugin_dir_path( __FILE__ ) . '/src/shortcode/shortcode.class.register.php' );


final class AdvertCode {
	
	public function __construct() {
		return;
	}

	public static function AddformEnqueue( $params ) {
		if ($params instanceof \stdClass) {
			\wp_enqueue_style( 'advert', \plugins_url('/assets/css/advert.css', __FILE__), array());
			\wp_enqueue_style( 'air-datepicker', \plugins_url('/libraries/node_modules/air-datepicker/dist/css/datepicker.css', __FILE__), [ 'advert' ]);
			\wp_enqueue_script( 'air-datepicker', \plugins_url('/libraries/node_modules/air-datepicker/dist/js/datepicker.min.js', __FILE__), [ 'jquery' ]);
			\wp_enqueue_script( 'datepicker-lang-fr', \plugins_url('/libraries/node_modules/air-datepicker/dist/js/i18n/datepicker.fr.js', __FILE__), [ 'air-datepicker' ]);
			\wp_enqueue_script( 'AddFormapp', \plugins_url('/assets/js/addform/addform.js', __FILE__), [ 'angular' ]);
			\wp_enqueue_script( 'addform-directive', \plugins_url('/assets/js/addform/addform.directive.js', __FILE__), [ 'AddFormapp' ] );
			\wp_enqueue_script( 'addform-factory', \plugins_url('/assets/js/addform/addform.factory.js', __FILE__), [ 'AddFormapp' ] );
			\wp_enqueue_script( 'addform-controller', \plugins_url('/assets/js/addform/addform.controller.js', __FILE__), [ 'AddFormapp', 'addform-directive', 'addform-factory' ] );
			\wp_localize_script( 'AddFormapp', 'advert', array(
				'ajax_url' => \admin_url( 'admin-ajax.php' ),
				'post_id' => $params->post_id,
				'vendors' => $params->vendors,
				'products_cat_child' => $params->product_cat,
				'assets_plugins_url' => \plugins_url( "/assets/", __FILE__ )
			));
		}
	}

	public static function AdvertsEnqueue( $params ) {
		if ( ! $params instanceof \stdClass) die( 'Variable params isn\'t instance of stdClass' );
		\wp_enqueue_style('material-icon', 'https://fonts.googleapis.com/icon?family=Material+Icons');
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
			'thumbnails' => $params->thumbnails,
			'posts' => $param->posts
		] );
	}

	public static function RegisterEnqueue() {
		\wp_enqueue_script('Register', \plugins_url('/assets/js/register/register.js', __FILE__), array('angular'));
		\wp_enqueue_script('RegisterFactory', \plugins_url('/assets/js/register/register.factory.js', __FILE__), array('angular'));
		\wp_enqueue_script('AdvertRegisterCtrl', \plugins_url('/assets/js/register/register.controller.js', __FILE__), array('angular'));
		\wp_localize_script('AdvertRegisterCtrl', 'advert', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__)
		));
	}

	public static function DashboardEnqueue( $user ) {
		\wp_enqueue_script( 'moment', \plugins_url('/assets/components/moment/moment-with-locales.min.js', __FILE__), [] );
		\wp_enqueue_script( 'angular-route', \plugins_url('/assets/components/angular-route/angular-route.min.js', __FILE__), ['angular'] );
		\wp_enqueue_script( 'DashboardAdvertModule', \plugins_url('/assets/js/dashboard/dashboard.js', __FILE__), array('angular'));
		\wp_enqueue_script( 'DashboardAdvertFactory', \plugins_url('/assets/js/dashboard/dashboard.factory.js', __FILE__), array('angular', "DashboardAdvertModule"));
		\wp_enqueue_script( 'routeDashboard', \plugins_url('/assets/js/route/dashboard.route.js', __FILE__), array('angular', "DashboardAdvertModule"));
		\wp_enqueue_script( 'DashboardAdvertController', \plugins_url('/assets/js/dashboard/dashboard.controller.js', __FILE__), array('angular', "DashboardAdvertModule"));
		\wp_localize_script( 'DashboardAdvertController', 'jsDashboard', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'logout_url' => \wp_logout_url( home_url( '/' )),
			'partials_uri' => \plugins_url( '/assets/js/route/partials/', __FILE__ ),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__),
			'_user' => $user
		));
	}

	public static function LoginEnqueue() {
		\wp_enqueue_script('LoginAdvertCtrl', \plugins_url('/assets/js/login/login.advert.js', __FILE__), array('angular'));
		\wp_localize_script('AdvertCtrl', 'advert', array(
			'ajax_url' => \admin_url('admin-ajax.php'),
			'assets_plugins_url' => \plugins_url('/assets/', __FILE__)
		));
	}
	
	public static function setEnqueue(){
		\wp_enqueue_style('jquery' );
		\wp_enqueue_style('dashicons' );
		\wp_enqueue_style('custom-style', \plugins_url('/assets/css/custom.css', __FILE__), []);
		\wp_enqueue_script('underscore', \plugins_url('/libraries/underscore/underscore.js', __FILE__));
		\wp_enqueue_script('angular', \plugins_url('/assets/components/angular/angular.js', __FILE__), array('jquery'));
		\wp_enqueue_script('aria', \plugins_url('/assets/components/angular-aria/angular-aria.min.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-messages', \plugins_url('/assets/components/angular-messages/angular-messages.min.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-animate', \plugins_url('/assets/components/angular-animate/angular-animate.min.js', __FILE__), array('angular'));
		\wp_enqueue_script('angular-sanitize', \plugins_url('/assets/components/angular-sanitize/angular-sanitize.js', __FILE__), array('angular'));
		
	}

	public static function setAngularMaterial() {
		\wp_enqueue_script('material', \plugins_url('/assets/components/angular-material/angular-material.min.js', __FILE__), array('angular'));
		\wp_enqueue_style('material-style', \plugins_url('/assets/components/angular-material/angular-material.css', __FILE__));
		\wp_enqueue_style('material-icon', 'https://fonts.googleapis.com/icon?family=Material+Icons');
	}

	public static function setUIKit() {
		\wp_enqueue_style('uikit-style', \plugins_url('/assets/components/uikit/css/uikit.css', __FILE__), []);
		\wp_enqueue_script('uikit', \plugins_url('/assets/components/uikit/js/uikit.min.js', __FILE__), ['jquery']);
		\wp_enqueue_script('uikit-icon', \plugins_url('/assets/components/uikit/js/uikit-icon.min.js', __FILE__), array('uikit-style'));
	}

}
