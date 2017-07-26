<?php

/*
  Plugin Name: FALI. Advert
  Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
  Description: --------.
  Version: 1.2
  Author: Tiafeno Finel
  Author URI: http://falicrea.com
  License: A "Slug" license name e.g. GPL2
 */

include_once plugin_dir_path(__FILE__) . '/advertcode.php';
include_once plugin_dir_path(__FILE__) . '/src/controller/AdvertController.class.php';
include_once plugin_dir_path(__FILE__) . '/entity/model/AdvertModel.php';
include_once plugin_dir_path(__FILE__) . '/widgets/search.widget.php';
include_once plugin_dir_path(__FILE__) . '/widgets/premium.widget.php';
$TWIG = null;

add_action('plugins_loaded', function() {
    //include_once plugin_dir_path(__FILE__) . '/engine/Twig/vendor/autoload.php';
    global $loader, $TWIG;
    if (!$loader instanceof Twig_Loader_Filesystem) { return; }

    if (!defined('ADVERT_TWIG_ENGINE_PATH')) {
        define('ADVERT_TWIG_ENGINE_PATH', plugin_dir_path(__FILE__) . '/engine/Twig');
    }

    //$loader = new Twig_Loader_Filesystem();
    $loader->addPath(ADVERT_TWIG_ENGINE_PATH . '/templates/front', 'frontadvert');
    $loader->addPath(ADVERT_TWIG_ENGINE_PATH . '/templates/admin', 'adminadvert');

    $TWIG = new Twig_Environment($loader, array(
        'debug' => true,
        'cache' => ADVERT_TWIG_ENGINE_PATH . '/template_cache'
    ));
});

class FALI_Advert extends AdvertController {
    private $Model;

    public function __construct() {
      parent::__construct();
      // Action WP
      add_action('init', array($this, '__init'));
      add_action('widgets_init', function () {
          register_widget('search_Widget');
      });
      add_action('admin_menu', function() {
          add_menu_page('Advert', 'Advert', 'manage_options', 'advert', array($this, 'advert_admin_template'), 'dashicons-admin-settings');
      });
      add_action('wp_loaded', function () {
          if (isset($_POST['setAdvert'], $_POST['post_id']) &&
          wp_verify_nonce($_POST['setAdvert_nonce_'], 'Advert_update_nonce') &&
          current_user_can('edit_post', $_POST['post_id'])) {
              $this->_action_add_new_advert();
          }
      });

      // Shortcode WP
      add_shortcode('st_advert', array('AdvertCode', 'RenderAddForm'));
        add_shortcode('st_register_advert', array('AdvertCode', 'RenderRegisterForm'));
      // Attributs
      $this->Model = new AdvertModel();
      //Install and Uninstall Plugins
      register_activation_hook(__FILE__, array('AdvertModel', 'install'));
      register_uninstall_hook(__FILE__, array('AdvertModel', 'uninstall'));
    }

    public function __init() {
        add_action('wp_ajax_action_set_thumbnail_post', array($this, 'action_set_thumbnail_post'));
        add_action('wp_ajax_nopriv_action_set_thumbnail_post', array($this, 'action_set_thumbnail_post'));

        add_action('wp_ajax_action_add_new_advert', array($this, 'action_add_new_advert'));
        add_action('wp_ajax_nopriv_action_add_new_advert', array($this, 'action_add_new_advert'));

        add_action('wp_ajax_action_register_user', array($this, 'action_register_user'));
        add_action('wp_ajax_nopriv_action_register_user', array($this, 'action_register_user'));

        add_action('wp_ajax_action_delete_post', array($this, 'action_delete_post'));
        add_action('wp_ajax_nopriv_action_delete_post', array($this, 'action_delete_post'));

        add_action('wp_ajax_action_set_thumbnail_id', array($this, 'action_set_thumbnail_id'));
        add_action('wp_ajax_nopriv_action_set_thumbnail_id', array($this, 'action_set_thumbnail_id'));
        // AdvertController.class.php
        add_action('wp_ajax_getTermsProductCategory', array($this, 'getTermsProductCategory'));
        add_action('wp_ajax_nopriv_getTermsProductCategory', array($this, 'getTermsProductCategory'));
        add_action('wp_ajax_getParentsTermsCat', array($this, 'getParentsTermsCat'));
        add_action('wp_ajax_nopriv_getParentsTermsCat', array($this, 'getParentsTermsCat'));

        register_taxonomy(
      		'district',
      		'product',
      		array(
      			'label' => __( 'Districte' ),
      			'rewrite' => array( 'slug' => 'district' ),
            'hierarchical' => true,
            'show_ui' => true
      		)
      	);

        return true;
    }

    public function action_set_thumbnail_post() {
        if (isset($_REQUEST['thumbnail_upload_nonce'], $_REQUEST['post_id']) &&
        wp_verify_nonce($_REQUEST['thumbnail_upload_nonce'], 'thumbnail_upload') &&
        current_user_can('edit_post', $_REQUEST['post_id'])
        ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );

            $attachment_id = media_handle_upload('file', $_REQUEST['post_id']);

            if (is_wp_error($attachment_id)) {
                wp_send_json(array('msg' => 'There was an error uploading the image.', 'type' => 'error'));
            } else {
                update_post_meta((int)$_REQUEST['post_id'], '_thumbnail_id', $attachment_id);
                wp_send_json(array(
                  'msg' => 'The image was uploaded successfully!',
                  'attach_id' => $attachment_id,
                  'url' => wp_get_attachment_image_src($attachment_id, array(250, 250))[0],
                  'type' => 'success')
                );
            }
        } else {
            wp_send_json(array('msg' => 'The security check failed, maybe show the user an error.', 'type' => 'error'));
        }
        die();
    }

    public function action_set_thumbnail_id(){
      if(isset($_REQUEST['attachment_id']) || isset($_REQUEST['post_id'])):
        $attach = (int)$_REQUEST['attachment_id'];
        $post_id = (int)$_REQUEST['post_id'];
        if(!is_int($post_id)) return false;

        $this->setter->setThumbnailbyRequestPostId($attach, $post_id);
      endif;
    }

    public function action_add_new_advert() {
        $post_id = (int) $_REQUEST['post_id'];
        $cost = (float)$_REQUEST['cost'];
        $gallery = json_decode($_REQUEST['gallery']);
        if (!is_array($gallery)) $gallery = array();

        //add_post_meta($post_id, '_thumbnail_id', '');
        wp_set_object_terms($post_id, 'simple', 'product_type');

        update_post_meta($post_id, '_visibility', 'visible');
        update_post_meta($post_id, '_stock_status', 'instock');
        update_post_meta($post_id, 'total_sales', '0');
        update_post_meta($post_id, '_downloadable', 'no');
        update_post_meta($post_id, '_virtual', 'yes');
        update_post_meta($post_id, '_regular_price', $cost);
        update_post_meta($post_id, '_sale_price', '');
        update_post_meta($post_id, '_purchase_note', '');
        update_post_meta($post_id, '_featured', 'no');
        update_post_meta($post_id, '_weight', '');
        update_post_meta($post_id, '_length', '');
        update_post_meta($post_id, '_width', '');
        update_post_meta($post_id, '_height', '');
        update_post_meta($post_id, '_sku', strtoupper(md5($post_id)));
        update_post_meta($post_id, '_product_attributes', array());
        update_post_meta($post_id, '_sale_price_dates_from', '');
        update_post_meta($post_id, '_sale_price_dates_to', '');
        update_post_meta($post_id, '_price', $cost);
        update_post_meta($post_id, '_sold_individually', '');
        update_post_meta($post_id, '_manage_stock', 'no');
        update_post_meta($post_id, '_backorders', 'no');
        update_post_meta($post_id, '_stock', '');
        update_post_meta($post_id, '_product_image_gallery', implode(",", $gallery));

        $desc = strip_shortcodes($_REQUEST['description']);
        $desc = apply_filters('the_content', $desc);
        $post = ['ID'     => $post_id,
            'post_status' => 'publish',
            /* @var $_REQUEST String */
            'post_title'   => esc_html((string)$_REQUEST['title']),
            'post_content' => $desc,
            ];
        $form = new stdClass();
        $form->state = $_REQUEST['state'];
        $form->adress = $_REQUEST['adress'];
        $form->phone = $_REQUEST['phone'];
        $form->hidephone = $_REQUEST['hidephone'];
        $form->post_id = $post_id;

        /* @var $post_id int */
        $post_id = wp_update_post( $post, true );
        if (is_wp_error($post_id)) {
                $errors = $post_id->get_error_messages();
                wp_send_json(array('msg' => 'Add annonce error : '.$errors, 'type' => 'error'));
        } else{
          $setAdvert = $this->Model->setAdvert($form); // return true if success or String if error
          if($setAdvert == true){
            wp_send_json(array('msg' => 'Add annonce success', 'type' => 'success'));
          } else{
            wp_send_json(array('msg' => 'Add annonce error : '.$setAdvert, 'type' => 'error'));
          }

        }
    }

    public function req($k, $def=''){
        return isset($_REQUEST[$k]) ? $_REQUEST[$k] : $def;
    }

    public function action_register_user(){
        if(is_user_logged_in())
            return false;

        if(!isset($_REQUEST['email']) && !isset($_REQUEST['password']))
            return false;

        //extract($_REQUEST, EXTR_PREFIX_SAME, "register");
//        wp_send_json($_REQUEST);
        if (isset($_REQUEST['email'])) {
            $user_id = username_exists($_REQUEST['email']);
        } else return false;

        if (isset($_REQUEST['lastname'])) {
            if ( !$user_id and email_exists($_REQUEST['email']) == false ) {
                if (isset($_REQUEST['password'])) {
                    $user_id = wp_create_user( sanitize_title($_REQUEST['lastname']),
                        $_REQUEST['password'],
                        $_REQUEST['email'] );
                    if($user_id){
                        set_user_role($user_id, 'editor');
                    }
                } else return false;
                wp_send_json(array('type'=>'success', 'message'=> $user_id));
            } else {
                wp_send_json(array('type' => 'Error', 'message' => 'User already exists.  Password inherited.'));
            }
        } else return false;
    }

    public function action_delete_post(){
      if(!is_user_logged_in())
        return false;

      if(!isset($_REQUEST['id']))
        return false;
      $post_id = (int)$_REQUEST['id'];
      //wp_delete_post($post_id);
      wp_send_json(wp_delete_attachment($post_id));
    }

    public function action_edit_post(){

    }

    public function action_upload_img() {
        /*
         * https://codex.wordpress.org/Function_Reference/media_handle_upload
         * https://codex.wordpress.org/Function_Reference/wp_insert_attachment
         */
    }

    public function advert_admin_template() {

    }

}

new FALI_Advert();
