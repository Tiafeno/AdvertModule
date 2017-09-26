<?php
namespace advert\loader;

$twig = null;
$login_fail = null;

add_action('plugins_loaded', function() {
  global $loader, $twig;
  if (!$loader instanceof \Twig_Loader_Filesystem) { return; }
  if (!defined('ADVERT_TWIG_ENGINE_PATH')) {
    define('ADVERT_TWIG_ENGINE_PATH', \plugin_dir_path(__FILE__) . '/engine/Twig');
  }
  
  /* @var $loader is instance of Twig_Loader_Filesystem
  ** $loader = new Twig_Loader_Filesystem();
  */
  $loader->addPath(ADVERT_TWIG_ENGINE_PATH . '/templates/front', 'frontadvert');
  $loader->addPath(ADVERT_TWIG_ENGINE_PATH . '/templates/admin', 'adminadvert');
  if (!is_null( $twig )) exit( 'Twig template is already define' );
  $twig = new \Twig_Environment($loader, array(
    'debug' => true,
    'cache' => ADVERT_TWIG_ENGINE_PATH . '/template_cache'
  ));
});