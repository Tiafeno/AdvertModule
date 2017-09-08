<?php
namespace advert;
/*
Plugin Name: WP AdVert
Plugin URI: https://github.com/Tiafeno/AdvertModule
Description: WP AdVert plugins, It is a module to make announcements of products to sell...
Version: 3.0
Author: Tiafeno Finel
Author URI: http://falicrea.com
License: A "Slug" license name e.g. GPL2
*/
include_once( plugin_dir_path(__FILE__) . '/autoload.php' );
include_once( plugin_dir_path(__FILE__) . '/libraries/php/parsedown/Parsedown.php' );
include_once( plugin_dir_path(__FILE__) . '/src/services/services.controller.php' );
include_once( plugin_dir_path(__FILE__) . '/src/controller/AdvertController.php' );
include_once( plugin_dir_path(__FILE__) . '/advert.php' );
include_once( plugin_dir_path(__FILE__) . '/advertcode.php' );
include_once( plugin_dir_path(__FILE__) . '/entity/model/AdvertModel.php' );

include_once( plugin_dir_path(__FILE__) . '/widgets/search.widget.php' );
include_once( plugin_dir_path(__FILE__) . '/widgets/premium.widget.php' );

use advert\plugins;

new plugins\_Advert();