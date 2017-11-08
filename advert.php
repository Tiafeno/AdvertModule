<?php
namespace advert\plugins;

use advert\src\controller\AdvertController as AdvertController;
use advert\entity\model\AdvertModel as AdvertModel;
use advert\libraries\parsedown as parsedown;
use advert\libraries\php\underscore\__ as __;

use advert\shortcode\AdvertCode as AdvertCode;
use advert\shortcode\addform as addform;
use advert\shortcode\adverts as adverts;
use advert\shortcode\dashboard as dashboard;
use advert\shortcode\login as login;
use advert\shortcode\register as register;

use advert\src\services\url as UrlServices;
use advert\src\services as services;

final class _Advert extends AdvertController {
  private $Model;
  public $Services;

  public function __construct() {
    parent::__construct();

    /* create Model instance */
    $this->Model = new AdvertModel();
    $this->Services = new services\ServicesController();
    
    // Action WP
    \add_action( 'init', array( &$this, 'wordpress_init' ));
    \add_action( 'search_Widget', function () {
      \register_widget( 'search_Widget' );
    });
    \add_action( 'admin_menu', function() {
      \add_menu_page('Advert', 'Advert', 'manage_options', 'advert', array(&$this, 'advert_admin_template'), 'dashicons-admin-settings');
    });

    \add_action( 'wp_loaded', [ &$this, 'wordpress_loaded' ]);
    \add_action( 'admin_init', [ &$this, 'admin_access' ], 100 );
    \add_action( 'after_setup_theme', [ &$this, 'remove_admin_bar' ]);
    \add_action( 'wp_login_failed', [ &$this, 'login_fail' ] );  /* On login fail */
    \add_action( 'user_register', [ &$this->Model, 'add_user' ], 10, 1 ); /* On register user success */
    \add_action( 'before_delete_post', [ &$this, 'verify_before_delete' ], 10, 1);
    \add_action( 'get_header', [ &$this, 'load_header' ], 10, 1);

    // Custom adVert shortcode Wordpress
    \add_shortcode('addform_advert', [ new addform\AddformCode(), 'Render' ]);
    \add_shortcode('adverts', [ new adverts\AdvertsCode(), 'Render' ]);
    \add_shortcode('login_advert', [ new login\LoginCode(), 'Render' ]);
    \add_shortcode('singin_advert', [ new register\RegisterCode(), 'Render' ]);
    \add_shortcode('dashboard_advert', [ new dashboard\DashboardCode(), 'Render' ]);

    /* Activate, Deactivate and Uninstall Plugins */
    \register_activation_hook( \plugin_dir_path( __FILE__ ) . 'init.php', array('_Advert', 'install'));
    \register_deactivation_hook( \plugin_dir_path( __FILE__ ) . 'init.php', array('_Advert', 'deactivate'));
    \register_uninstall_hook( \plugin_dir_path( __FILE__ ) . 'init.php', array('_Advert', 'uninstall'));
  }
  /* Hook register function */
  public static function uninstall(){ return AdvetModel::uninstall(); }
  public static function deactivate() { return AdvertModel::deactivate(); }
  public static function install() { return AdvertModel::install(); }

  /** * Begin action */
  public function admin_access() {
    $redirect = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? $_SERVER[ 'HTTP_REFERER' ] : \home_url( '/' );
    if ( \is_admin() && !defined( 'DOING_AJAX' ) && \current_user_can( 'advertiser' ) ) {
      exit( \wp_redirect( $redirect, 301 ) );
    }
  }

  public function remove_admin_bar() {
    if (!\current_user_can( 'administrator' ) && !is_admin()) {
      \show_admin_bar( false );
    }
  }

  public function login_fail() {
    $referrer = $_SERVER[ 'HTTP_REFERER' ];
    // if there's a valid referrer, and it's not the default log-in screen
    if ( !empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin') ) {
        exit(\wp_redirect( $referrer . '?login=failed', 301 ));  // let's append some information (login=failed) to the URL for the theme to use
    }
  }
  /** * End action */

  public function wordpress_init() {
    \add_action('wp_ajax_action_set_thumbnail_post', array($this, 'action_set_thumbnail_post'));
    \add_action('wp_ajax_nopriv_action_set_thumbnail_post', array($this, 'action_set_thumbnail_post'));

    \add_action('wp_ajax_action_add_new_advert', array($this, 'action_add_new_advert'));

    \add_action('wp_ajax_action_register_user', array($this, 'action_register_user'));
    \add_action('wp_ajax_nopriv_action_register_user', array($this, 'action_register_user'));

    \add_action('wp_ajax_action_delete_post', array($this, 'action_delete_post'));

    \add_action('wp_ajax_action_set_thumbnail_id', array($this, 'action_set_thumbnail_id'));
    \add_action('wp_ajax_nopriv_action_set_thumbnail_id', array($this, 'action_set_thumbnail_id'));

    \add_action('wp_ajax_action_send_mail', array($this, 'action_send_mail'));
    \add_action('wp_ajax_nopriv_action_send_mail', array($this, 'action_send_mail'));

    \add_action('wp_ajax_action_update_dashboard', array($this, 'action_update_dashboard'));

    /* See these function at AdvertController.class.php */
    \add_action('wp_ajax_action_get_shops', array($this, 'action_get_shops'));
    \add_action('wp_ajax_nopriv_action_get_shops', array($this, 'action_get_shops'));

    \add_action('wp_ajax_getTermsProductCategory', array($this, 'getTermsProductCategory'));
    \add_action('wp_ajax_nopriv_getTermsProductCategory', array($this, 'getTermsProductCategory'));

    \add_action('wp_ajax_action_get_vendors', array($this, 'action_get_vendors'));
    \add_action('wp_ajax_nopriv_action_get_vendors', array($this, 'action_get_vendors'));

    \add_action('wp_ajax_getParentsTermsCat', array($this, 'getParentsTermsCat'));
    \add_action('wp_ajax_nopriv_getParentsTermsCat', array($this, 'getParentsTermsCat'));

    \add_action('wp_ajax_action_get_advertdetails', array($this, 'action_get_advertdetails'));
    \add_action('wp_ajax_nopriv_action_get_advertdetails', array($this, 'action_get_advertdetails'));

    \add_action('wp_ajax_action_verify_password', array($this, 'action_verify_password'));
    \add_action('wp_ajax_nopriv_action_verify_password', array($this, 'action_verify_password'));

    \add_action('wp_ajax_action_change_password', array($this, 'action_change_password'));

    \add_action('wp_ajax_action_get_nonce', array($this, 'action_get_nonce'));

    \add_action('wp_ajax_action_render_nonce', array($this, 'action_render_nonce'));
    \add_action('wp_ajax_nopriv_action_render_nonce', array($this, 'action_render_nonce'));

    \add_action('wp_ajax_action_edit_post_verify', array($this, 'action_edit_post_verify'));
    \add_action('wp_ajax_nopriv_action_edit_post_verify', array($this, 'action_edit_post_verify'));

    \add_action('wp_ajax_action_upload_avatar', array($this, 'action_upload_avatar'));
    \add_action('wp_ajax_action_update_product', array($this, 'action_update_product'));
    \add_action('wp_ajax_action_delete_product', array($this, 'action_delete_product'));

    \register_taxonomy(
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

  /*
  ** This function is an wordpress action 'get_header'
  */
  public function load_header( $name ) {
    global $post;
    if (\is_user_logged_in()) {
      $login_page_id = \get_option( 'login_page_id', false );
      if (is_int( (int)$login_page_id ) ) :
        if ($post->ID != (int)$login_page_id) return true;
          $url = \home_url( "/" );
          $dashboad_page_id = \get_option( 'dashboard_page_id', false );
          if ($dashboad_page_id)
            $url = \get_permalink( $dashboad_page_id );
          \wp_redirect( $url );
          exit();
      endif;
    }
  }

  /*
  * This function load on wordpress load action
  * @function wordpress_loaded
  * @return void
  */
  public function wordpress_loaded() {
    //print_r(services\ServicesController::getShops());

    if (isset( $_POST[ 'setAdvert' ], $_POST[ 'post_id' ] ) &&
    \wp_verify_nonce($_POST[ 'setAdvert_nonce_' ], 'Advert_update_nonce') &&
    \current_user_can('edit_post', $_POST[ 'post_id' ])) {
      $this->_action_add_new_advert();
    }

    if (isset($_POST[ 'advert_settings_nonce' ]) &&
    \wp_verify_nonce($_POST[ 'advert_settings_nonce' ], 'advert_settings') &&
    is_admin() ) {

      $register_page_id = (int) $_POST[ 'register_page' ];
      $addform_page_id = (int) $_POST[ 'addform_page' ];
      $login_page_id = (int) $_POST[ 'login_page' ];
      $dashboard_page_id = (int) $_POST[ 'dashboard_page' ];

      \update_option( 'register_page_id', $register_page_id );
      \update_option( 'addform_page_id', $addform_page_id );
      \update_option( 'login_page_id', $login_page_id );
      \update_option( 'dashboard_page_id', $dashboard_page_id );
    }

    if (isset($_GET[ 'login' ])) {
      $value = trim($_GET[ 'login' ]);
      if ($value != 'failed') return;
      global $login_fail;
      $login_fail = 'Invalid username or incorrect password.';
    }
  }

  /*
  ** @function action_set_thumbnail_post
  ** This function upload a image
  ** @action, shortcode addform
  ** @param, void
  ** @return, json type
  */
  public function action_set_thumbnail_post() {
    $User = null;
    if (isset($_REQUEST[ 'post_id' ])) {
      $post_id = (int) $_REQUEST[ 'post_id' ];
    } else {
      \wp_send_json(array(
          'data' => 'Variable post_id don\'t define on $http.',
          'tracking' => null,
          'type' =>  false
        )
      );
    }
    if (isset($_REQUEST[ 'thumbnail_upload_nonce' ]) &&
      \wp_verify_nonce($_REQUEST[ 'thumbnail_upload_nonce' ], 'thumbnail_upload')
    ) {
      if (!is_int( (int)$_REQUEST[ 'post_id' ])) return;
      require_once( ABSPATH . 'wp-admin/includes/image.php' );
      require_once( ABSPATH . 'wp-admin/includes/file.php' );
      require_once( ABSPATH . 'wp-admin/includes/media.php' );
      $attachment_id = \media_handle_upload( 'file', (int)$_REQUEST[ 'post_id' ] );
      if (\is_wp_error( $attachment_id )) {
        \wp_send_json(array(
          'data' => 'There was an error uploading the image. Probably, the uploaded file exceeds the upload_max_filesize.',
          'tracking' => $attachment_id->get_error_messages(),
          'type' => false)
        );
      } else {
        if ( ! in_array( '_thumbnail_id', \get_post_custom_keys( $post_id ) )) :
          \update_post_meta($post_id, '_thumbnail_id', $attachment_id);
        else:
          $product_gallery_exist = in_array( '_product_image_gallery', \get_post_custom_keys( $post_id ) );
          if ($product_gallery_exist) {
            $gallery = \get_post_meta( $post_id, '_product_image_gallery', true );
            $gallery_id = explode( ',', $gallery );
            array_push( $gallery_id, $attachment_id );
          } 
          $gallery_value = $product_gallery_exist ? implode( ",", $gallery_id ) : $attachment_id;
          \update_post_meta( $post_id, '_product_image_gallery', $gallery_value );
        endif;
        
        \wp_send_json(array(
          'data' => 'The image was uploaded successfully!',
          'attach_id' => $attachment_id,
          'url' => \wp_get_attachment_image_src($attachment_id, array(250, 250))[ 0 ],
          'type' => true)
        );
      }
    } else {
      \wp_send_json(array(
          'data' => 'The security check failed, maybe show the user an error.',
          'tracking' => [ 'post_id' => $post_id ],
          'type' => false
        )
      );
    }
    die();
  }

  /**
  * Update or set post a attachment thumbnail
  *
  * @function action_set_thumbnail_id
  * This is a action $http function, action update or set a post an a thumbnail id
  *
  * @param, void
  * @return, JSON : with `type` value true or false. 0 if user isn't logged
  **/
  public function action_set_thumbnail_id(){
    $attachment_id = (int) services\Request::req( 'attachment_id', false );
    $post_id = (int) services\Request::req( 'post_id', false );
    if ($attachment_id != false || $post_id != false) :
      if (!is_int( $post_id )) return false;
      $this->Services->setThumbnail($attachment_id, $post_id);
    endif;
  }

  public function action_send_mail() {
    $post_id = services\Request::req('post_id', false);
    $senderName = services\Request::req( 'senderName', false );
    $sender = services\Request::req( 'sender', false );
    $message = services\Request::req( 'message', false );
    $User = null;
    $headers = [];

    if (\is_user_logged_in())
      $User = \wp_get_current_user();
    if (false === $sender || false === $senderName || false === $message || false === $post_id) 
      \wp_send_json( ['error' => 'Probably one of the parameters is not defined', 'send' => false] );
    $Author = services\ServicesController::getAuthor( $post_id );

    /* prepare to send mail */
    $subject = "Contact - " . $Author[ 'post_title'];
    $to = $Author[ 'mail' ];
    $body = &$message;
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . \esc_html( $senderName ) . ' <' . $sender . '>';
    $headers[] = 'Cc: contact@falicrea.com'; 
    $headers[] = 'Cc: ' . $sender; 
    if (\wp_mail( $to, $subject, $body, $headers)) :
      \wp_send_json( ['send' => true, 'data' => 'Your message has been sent successfully'] );
    endif;
    \wp_send_json( ['error' => 'An error occurred while sending', 'send' => false] );
  }

  /**
  * This function Update user profil
  * @function action_update_dashboard
  * @param, void
  * @return, json object send
  **/
  public function action_update_dashboard( $paramNonce = false ) {
    $Nonce = services\Request::req( 'inputNonce', $paramNonce );
    if ($Nonce === false) \wp_send_json([ 'Missing Nonce!' ]);
    if (!\wp_verify_nonce( $Nonce, _update_profil_nonce_ ) ) \wp_send_json([ 'Nonce isn\'t valide' ]);
    if (!\is_user_logged_in()) return false;
    $User = \wp_get_current_user();
    $params = $_REQUEST;
    $where = [ 'id_user' => $User->ID ];
    $wParams = (object) $params;
    $data = __::without( $params,
      $wParams->action,
      $wParams->user_login,
      $wParams->user_email,
      $wParams->user_registered,
      $wParams->user_nicename,
      $wParams->display_name,
      $wParams->add_date,
      $wParams->token
    );
    /* Update user advert */
    $update_user = $this->Model->update_user( $data, $where );
    if (true === $update_user) {
      /* Update user user_login */
      global $wpdb;
      if (trim($params[ 'user_login' ]) != trim($User->user_login)) {
        $update_usr = $wpdb->update( $wpdb->users,
        [ 'user_login' => trim($params[ 'user_login' ]) ],
        [ 'ID' => $User->ID ]);
        $wpdb->flush();
        if (false === $update_usr) {
          \wp_send_json( [
            'type' => false, 'data' => 'Error on update user_login']
          );
        } else {
          \wp_send_json([
            'type' => true, 'reload' => true, 'data' => 'User profil and login name update with success'
          ]);
        }
      } else {
        \wp_send_json([
          'type' => true, 'data' => 'User update with success'
        ]);
      }
    } else \wp_send_json( [
      'type' => false,
      'data' => 'Error on update profil user',
      'tracking' => $update_user
    ] );
  }

  /**
  * Update the post content
  *
  * This is a action $http function, action update the product post type after submit
  * add form in front page.
  *
  * @function action_add_new_advert
  * @param, void
  * @return, JSON with type `false`,`true` and `0` if user isn't logged or request post_id not send
  **/
  public function action_add_new_advert() {
    if (!isset( $_REQUEST[ 'post_id' ] )) return false;
    if (!\is_user_logged_in()) return false;

    $post_id = (int)$_REQUEST[ 'post_id' ];
    $cost = (float)$_REQUEST[ 'cost' ];
    $gallery = json_decode( $this->req('gallery', []) );
    if (!is_array( $gallery )) $gallery = array();

    \wp_set_object_terms($post_id, 'simple', 'product_type');

    /* Update post meta, these meta depend a product post_type */
    \update_post_meta( $post_id, '_visibility', 'visible');
    \update_post_meta( $post_id, '_stock_status', 'instock');
    \update_post_meta( $post_id, 'total_sales', '0');
    \update_post_meta( $post_id, '_downloadable', 'no');
    \update_post_meta( $post_id, '_virtual', 'yes');
    \update_post_meta( $post_id, '_regular_price', $cost);
    \update_post_meta( $post_id, '_sale_price', '');
    \update_post_meta( $post_id, '_purchase_note', '');
    \update_post_meta( $post_id, '_featured', 'no');
    \update_post_meta( $post_id, '_weight', '');
    \update_post_meta( $post_id, '_length', '');
    \update_post_meta( $post_id, '_width', '');
    \update_post_meta( $post_id, '_height', '');
    \update_post_meta( $post_id, '_sku', strtoupper( md5( $post_id )) );
    \update_post_meta( $post_id, '_sale_price_dates_from', '');
    \update_post_meta( $post_id, '_sale_price_dates_to', '');
    \update_post_meta( $post_id, '_price', $cost);
    \update_post_meta( $post_id, '_sold_individually', '');
    \update_post_meta( $post_id, '_manage_stock', 'no');
    \update_post_meta( $post_id, '_backorders', 'no');
    \update_post_meta( $post_id, '_stock', '');
    \update_post_meta( $post_id, '_product_image_gallery', implode(",", $gallery));

    $desc = \apply_filters('the_content', $_POST[ 'description' ]);

    $post = [
      'ID'     => $post_id,
      'post_status'  => 'publish',
      'post_title'   => \esc_html((string)$_REQUEST[ 'title' ]),
      'post_content' => $desc
    ];

    /* Update the post for new content */
    $current_post_id = \wp_update_post( $post, true );
    if (\is_wp_error( $current_post_id )) {
      \wp_send_json(array(
          'data' => $current_post_id->get_error_messages(),
          'tracking' => 'Error: Update Post product ',
          'type' => false
        )
      );
    } else {
      /**
      * Update cutom post meta key
      */
      $form = new \stdClass();
      $form->state  = $this->req( 'state' );
      $form->adress = $this->req( 'adress' );
      $form->phone  = $this->req( 'phone' );
      $form->hidephone = $this->req( 'hidephone', 0 );
      $form->post_id = $post_id;
      
      \update_post_meta( $post_id, '_product_advert_state', $form->state );
      \update_post_meta( $post_id, '_product_advert_adress', $form->adress );
      \update_post_meta( $post_id, '_product_advert_phone', $form->phone );
      \update_post_meta( $post_id, '_product_advert_hidephone', $form->hidephone );
      
      /*
      * Set term product_cat to this post,
      * if the term exist in product_cat taxonomy
      */
      $this->_setTerms( $current_post_id );
    }
  }

  private function _setTerms( $post_id ) {
    $categorie = $this->req( 'categorie' );
    $term = \term_exists( (int) $categorie, 'product_cat');
    if (!is_null( $term )){
      $post_terms = \wp_set_post_terms( $post_id, [ $term[ 'term_id' ] ], 'product_cat');
      if (\is_wp_error( $post_terms ))
        \wp_send_json( [
          'type' => false,
          'tracking' => 'Error: On set post terms ',
          'data' => $post_terms->get_error_messages()
          ]
        );
    }
    
    /* Set attributes in the product post */
    $this->_setAttributs( $post_id );
  }

  private function _setAttributs( $post_id ) {
    if (false != $this->req( 'attributs', false )) {
      $attributes = str_replace('\\"','"', trim($this->req( 'attributs' )));
      $attributes = json_decode($attributes);
      $product_attributes = [];
      while (list(, $attribute) = each( $attributes )) {
        \wp_set_object_terms($post_id, $attribute->value, $attribute->_id);
        $product_attributes[ $attribute->_id ] = [
          'name' => $attribute->_id, // set attribute name
          'value' => (int)$attribute->value, // set attribute value
          'is_visible' => 1,
          'is_variation' => 0,
          'is_taxonomy' => 0 // !important
        ];
      }
      \update_post_meta($post_id, '_product_attributes', $product_attributes);
      \wp_send_json([
          'type' => true,
          'data' => 'Update post with attributs',
          'redirect_url' => UrlServices\ServiceUrlController::getAdvertDetailsUrl( $post_id )
        ]
      );
    } else {
      \wp_send_json( [
        'type' => true,
        'data' => 'Update post with success without attributs!'
        ]
      );
    }
  }

  /* This is Lambda function to get REQUEST header content */
  public static function req($k, $def = false){
    $request = trim($_REQUEST[ $k ]);
    return isset( $request ) ? ( !empty( $request ) ? $request : false ) : $def;
  }

  public function action_register_user() {
    if (\is_user_logged_in())
      return false;

    if (!isset( $_REQUEST[ 'email' ], $_REQUEST[ 'password' ] ))
      return false;
    $user_id = \username_exists( \sanitize_title($_REQUEST[ 'lastname' ]) );
    if (isset( $_REQUEST[ 'lastname' ], $_REQUEST[ 'firstname' ] )) {
      if (!$user_id && \email_exists( $_REQUEST[ 'email' ] ) == false ) {
        if (isset( $_REQUEST[ 'password' ] ) && !empty($_REQUEST['password'])) {
          /* @return id user */
          $user_id = \wp_create_user( \sanitize_title( trim($_REQUEST[ 'lastname' ]) ), $_REQUEST[ 'password' ], trim($_REQUEST[ 'email' ]) );
          if (!\is_wp_error($user_id)){
            /* Register success */
            $update_usr = \wp_update_user([
              'ID' => $user_id,
              'role' => 'advertiser'
            ]);
            $User = new \WP_User( $user_id );
            $User->add_cap('upload_files');
            if (!is_int($update_usr)) \wp_send_json(['Error on update user role, probably that user doesn\'t exist.']);
            $addform_page_id = \get_option( 'addform_page_id', false );
            $verify = $addform_page_id == false || !is_int( (int)$addform_page_id );
            $redirect_url =  $verify ? \get_home_url() : \get_the_permalink( (int)$addform_page_id );
            \wp_send_json([
              'type' => 'success',
              'data' => 'User add with success',
              'redirect_url' => $redirect_url
            ]);
          } else {
            \wp_send_json([
              'type' => 'error',
              'tracking' => 'Create user.',
              'data' => $user->get_error_messages()
            ]);
          }
        } else {
          \wp_send_json(array(
            'type' => 'error',
            'tracking' => 'Please review $_REQUEST variable, probably that `password` don\'t send or not define. ',
            'data' => 'Request `password` is not define.'
            )
          );
        }

        \wp_send_json(array(
          'type' => 'success',
          'data' => $user_id
          )
        );
      } else {
        \wp_send_json(array(
          'type' => 'error',
          'tracking' => 'Adress `email` or `user` already exists. ',
          'data' => 'User already exists.'
          )
        );
      }
    } else \wp_send_json([
      'type' => 'error',
      'tracking' => 'Please review Request variable, probably that `lastname` or `firstname` is not define.',
      'data' => 'There are variables not defined in the query.'
    ]);
  }

  public function action_delete_post() {
    if (!\is_user_logged_in())
      return false;

    if (false === services\Request::req( 'id', false))
      \wp_send_json( ['type' => false, 'data' => 'params ìd`missing'] );

    $post_id = (int)trim(services\Request::req( 'id' ));
    $post_type = ( false == services\Request::req( 'post_type', false ) ) ? trim($_REQUEST['post_type']) : 'attachment';
    $args = [
      'p' => $post_id,
      'post_type' => $post_type
    ];
    $attachment = new \WP_Query( $args );
    if ($attachment->have_posts()) {
      \wp_reset_postdata();
      $results = \wp_delete_attachment( $post_id );
      if ($results) {
        \wp_send_json( [
          'type' => true,
          'data' => 'Attachment delete with success',
          'ID' => $post_id
        ] );
      } else {
        \wp_send_json([
          'type' => false,
          'data' => 'Error on delete Attachment'
        ]);
      }

    } else {
      \wp_send_json( [
        'type' => false,
        'data' => 'Attachment doesn\'t exist'
      ] );
    }


  }

  public function action_edit_post() {

  }

  public function action_upload_img() {
    /*
    * https://codex.wordpress.org/Function_Reference/media_handle_upload
    * https://codex.wordpress.org/Function_Reference/wp_insert_attachment
    */
  }

  public function advert_admin_template() {
    global $twig;
    $params = [
      'post_type' => 'page',
      'posts_per_page' => -1
    ];
    $posts = \get_posts( $params );
    $args = [
      'nonce' => \wp_nonce_field('advert_settings', 'advert_settings_nonce'),
      'register_page_id'  => \get_option( 'register_page_id', false ),
      'login_page_id'     => \get_option( 'login_page_id', false),
      'addform_page_id'   => \get_option( 'addform_page_id', false ),
      'dashboard_page_id' => \get_option( 'dashboard_page_id', false ),
      'posts' => $posts
    ];
    print $twig->render('@adminadvert/settings.html', $args);
  }

}
