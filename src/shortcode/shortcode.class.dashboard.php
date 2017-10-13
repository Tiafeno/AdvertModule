<?php
namespace advert\shortcode\dashboard;
use advert\shortcode as shortcode;
use advert\src\services as Services;
use advert\shortcode\login as Login;

final class DashboardCode {
	public function __construct() {}
		
  /**
	* [dashboard_advert]
	**/
	public static function Render() {
		$current_user = null;
    if (\is_user_logged_in()) {
      $current_user = \wp_get_current_user();
		}

		if (is_null( $current_user )) return Login\LoginCode::Render();
		$user = Services\ServicesController::getUser( $current_user->ID );
		
		shortcode\AdvertCode::setEnqueue();
		shortcode\AdvertCode::setAngularMaterial();
		shortcode\AdvertCode::DashboardEnqueue( $user );
		global $twig;
		return $twig->render('@frontadvert/dashboard.html', array(
			
		));
	}
}
