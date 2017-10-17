<?php
namespace advert;
/*
Plugin Name: WP AdVert
Plugin URI: https://github.com/Tiafeno/AdvertModule
Description: WP AdVert plugins, It is a module to make announcements of products to sell...
Version: 3.4.2
Author: Tiafeno Finel
Author URI: http://falicrea.com
License: A "Slug" license name e.g. GPL2
*/
include_once( plugin_dir_path(__FILE__) . '/autoload.php' );
include_once( plugin_dir_path(__FILE__) . '/libraries/php/underscore/underscore.php' );
include_once( plugin_dir_path(__FILE__) . '/libraries/php/parsedown/Parsedown.php' );
include_once( plugin_dir_path(__FILE__) . '/src/services/services.controller.php' );

include_once( plugin_dir_path(__FILE__) . '/src/factory/factory.controller.php' );
include_once( plugin_dir_path(__FILE__) . '/src/services/services.url.controller.php' );
include_once( plugin_dir_path(__FILE__) . '/src/services/services.request.controller.php' );

include_once( plugin_dir_path(__FILE__) . '/src/controller/AdvertController.php' );

include_once( plugin_dir_path(__FILE__) . '/src/components/interfaces/user.interface.php' );
include_once( plugin_dir_path(__FILE__) . '/src/components/user.class.php' );
include_once( plugin_dir_path(__FILE__) . '/advert.php' );
include_once( plugin_dir_path(__FILE__) . '/advertcode.php' );
include_once( plugin_dir_path(__FILE__) . '/entity/model/AdvertModel.php' );

include_once( plugin_dir_path(__FILE__) . '/widgets/search.widget.php' );
include_once( plugin_dir_path(__FILE__) . '/widgets/premium.widget.php' );

/* Constant config advert */
define('_update_product_nonce_', 'update_product_nonce');
define('_update_profil_nonce_', 'update_profil_nonce');

use advert\plugins;
new plugins\_Advert();
