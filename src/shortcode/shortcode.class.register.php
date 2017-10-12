<?php
namespace advert\shortcode\register;
use advert\shortcode as shortcode;
use advert\shortcode\addform as addform;

final class RegisterCode {
  public function __construct() { return; }
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
      /* Render add form */
			return addform\AddformCode::Render([], null);

		shortcode\AdvertCode::setEnqueue();
		shortcode\AdvertCode::setAngularMaterial();
		shortcode\AdvertCode::RegisterEnqueue();
		$login_link = \get_option( 'login_page_id', false ) ? \get_permalink(\get_option( 'login_page_id', false )) : '#login';
		return $twig->render('@frontadvert/registerform.advert.html', array(
			'login_link' => $login_link
		));
		
		
	}
}