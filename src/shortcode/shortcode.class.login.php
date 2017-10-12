<?php
namespace advert\shortcode\login;
use advert\shortcode as shortcode;

final class LoginCode {
  public function __construct() {}

	/**
	* [login_advert]
	**/
	public static function Render() {
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
		
		/* set script and style  */
		shortcode\AdvertCode::setEnqueue();
		shortcode\AdvertCode::setUIKit();
		shortcode\AdvertCode::setAngularMaterial();
		shortcode\AdvertCode::LoginEnqueue();

		$register_link = \get_option( 'register_page_id', false ) ? \get_permalink(\get_option( 'register_page_id', false )) : '#register';
		return $twig->render('@frontadvert/loginform.advert.html', array(
			'login_fail' => $login_fail,
			'form_id' => $args['form_id'],
			'action' => \esc_url( \site_url( 'wp-login.php', 'login_post' ) ), //$_SERVER[ 'REQUEST_URI' ]
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
}