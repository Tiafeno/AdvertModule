<?php
namespace advert\plugins;

use advert\src\controller\AdvertController as AdvertController;
use advert\entity\model\AdvertModel as AdvertModel;
use shortcode\AdvertCode as AdvertCode;

final class _Advert extends AdvertController {
  private $Model;
  
  public function __construct() {
    parent::__construct();
    // Action WP
    \add_action('init', array( $this, '__init' ));
    \add_action('widgets_init', function () {
      \register_widget( 'search_Widget' );
    });
    \add_action('admin_menu', function() {
      \add_menu_page('Advert', 'Advert', 'manage_options', 'advert', array($this, 'advert_admin_template'), 'dashicons-admin-settings');
    });
    \add_action('wp_loaded', function () {
      if (isset( $_POST[ 'setAdvert' ], $_POST[ 'post_id' ] ) &&
      \wp_verify_nonce($_POST[ 'setAdvert_nonce_' ], 'Advert_update_nonce') &&
      \current_user_can('edit_post', $_POST[ 'post_id' ])) {
        $this->_action_add_new_advert();
      }

      if (isset($_POST[ 'advert_settings_nonce' ]) && 
      \wp_verify_nonce($_POST[ 'advert_settings_nonce' ], 'advert_settings')) {

        $register_page_id = (int) $_POST[ 'register_page' ];
        $addform_page_id = (int) $_POST[ 'addform_page'];

        \update_option( 'register_page_id', $register_page_id );
        \update_option( 'addform_page_id', $addform_page_id );
      }

    });
    // Shortcode WP
    \add_shortcode('addform_advert', [ new shortcode\AdvertCode(),'RenderAddForm']);
    \add_shortcode('adverts', [ new shortcode\AdvertCode(),'get_adverts']);
    \add_shortcode('singin_advert', [ new shortcode\AdvertCode(),'RenderRegisterForm']);
    $this->Model = new AdvertModel();

    /* Activate and Uninstall Plugins */
    \register_activation_hook( \plugin_dir_path( __FILE__ ) . 'init.php', array($this->Model, 'install'));
    \register_uninstall_hook( \plugin_dir_path( __FILE__ ) . 'init.php', array($this->Model, 'uninstall'));
  }
  
  public function __init() {
    \add_action('wp_ajax_action_set_thumbnail_post', array($this, 'action_set_thumbnail_post'));
    \add_action('wp_ajax_nopriv_action_set_thumbnail_post', array($this, 'action_set_thumbnail_post'));
    
    \add_action('wp_ajax_action_add_new_advert', array($this, 'action_add_new_advert'));
    \add_action('wp_ajax_nopriv_action_add_new_advert', array($this, 'action_add_new_advert'));
    
    \add_action('wp_ajax_action_register_user', array($this, 'action_register_user'));
    \add_action('wp_ajax_nopriv_action_register_user', array($this, 'action_register_user'));
    
    \add_action('wp_ajax_action_delete_post', array($this, 'action_delete_post'));
    \add_action('wp_ajax_nopriv_action_delete_post', array($this, 'action_delete_post'));
    
    \add_action('wp_ajax_action_set_thumbnail_id', array($this, 'action_set_thumbnail_id'));
    \add_action('wp_ajax_nopriv_action_set_thumbnail_id', array($this, 'action_set_thumbnail_id'));

    /* See these function at AdvertController.class.php */
    \add_action('wp_ajax_getTermsProductCategory', array($this, 'getTermsProductCategory'));
    \add_action('wp_ajax_nopriv_getTermsProductCategory', array($this, 'getTermsProductCategory'));

    \add_action('wp_ajax_getParentsTermsCat', array($this, 'getParentsTermsCat'));
    \add_action('wp_ajax_nopriv_getParentsTermsCat', array($this, 'getParentsTermsCat'));

    \add_action('wp_ajax_controller_getProduct', array($this, 'controller_getProduct'));
    \add_action('wp_ajax_nopriv_controller_getProduct', array($this, 'controller_getProduct'));
    
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
  
  public function action_set_thumbnail_post() {
    if (isset($_REQUEST[ 'thumbnail_upload_nonce' ], $_REQUEST[ 'post_id' ]) &&
    \wp_verify_nonce($_REQUEST[ 'thumbnail_upload_nonce' ], 'thumbnail_upload') &&
    \current_user_can('edit_post', $_REQUEST[ 'post_id' ])
    ) {
      if (!is_int( (int)$_REQUEST[ 'post_id' ])) return;
      require_once( ABSPATH . 'wp-admin/includes/image.php' );
      require_once( ABSPATH . 'wp-admin/includes/file.php' );
      require_once( ABSPATH . 'wp-admin/includes/media.php' );
      $attachment_id = \media_handle_upload('file', (int)$_REQUEST[ 'post_id' ]);
      if (\is_wp_error( $attachment_id )) {
        \wp_send_json(array('data' => 'There was an error uploading the image.', 'tracking' => null, 'type' => 'error'));
      } else {
        \update_post_meta((int)$_REQUEST[ 'post_id' ], '_thumbnail_id', $attachment_id);
        \wp_send_json(array(
          'data' => 'The image was uploaded successfully!',
          'attach_id' => $attachment_id,
          'url' => \wp_get_attachment_image_src($attachment_id, array(250, 250))[ 0 ],
          'type' => 'success')
        );
      }
    } else {
      \wp_send_json(array(
          'data' => 'The security check failed, maybe show the user an error.', 
          'tracking' => null, 
          'type' => 'error'
        )
      );
    }
    die();
  }
  
  public function action_set_thumbnail_id(){
    if (isset( $_REQUEST[ 'attachment_id' ] ) || isset( $_REQUEST[ 'post_id' ] )):
      $attachment_id = (int)$_REQUEST[ 'attachment_id' ];
      $post_id = (int)$_REQUEST[ 'post_id' ];
      if (!is_int( $post_id )) return false;
      
      $this->Services->setThumbnailbyRequestPostId($attachment_id, $post_id);
    endif;
  }

  /**
  * Update the post content
  *
  * This is a action $http function, action update the product post type after submit
  * add form in front page.
  *
  * @function action_add_new_advert
  * @param void
  * @return json, type `false` and `true`, `0` if user isn't login or request post_id not send 
  **/
    
  public function action_add_new_advert() {
    if (!isset( $_REQUEST[ 'post_id' ] )) return false;
    if (!\is_user_logged_in()) return false;

    $post_id = (int)$_REQUEST[ 'post_id' ];
    $cost = (float)$_REQUEST[ 'cost' ];
    $gallery = json_decode( $this->req($_REQUEST[ 'gallery' ], []) );
    if (!is_array( $gallery )) $gallery = array();
    
    \wp_set_object_terms($post_id, 'simple', 'product_type');

    /* Update post meta, these meta depend a product post_type */
    \update_post_meta($post_id, '_visibility', 'visible');
    \update_post_meta($post_id, '_stock_status', 'instock');
    \update_post_meta($post_id, 'total_sales', '0');
    \update_post_meta($post_id, '_downloadable', 'no');
    \update_post_meta($post_id, '_virtual', 'yes');
    \update_post_meta($post_id, '_regular_price', $cost);
    \update_post_meta($post_id, '_sale_price', '');
    \update_post_meta($post_id, '_purchase_note', '');
    \update_post_meta($post_id, '_featured', 'no');
    \update_post_meta($post_id, '_weight', '');
    \update_post_meta($post_id, '_length', '');
    \update_post_meta($post_id, '_width', '');
    \update_post_meta($post_id, '_height', '');
    \update_post_meta($post_id, '_sku', strtoupper( md5( $post_id )) );
    \update_post_meta($post_id, '_sale_price_dates_from', '');
    \update_post_meta($post_id, '_sale_price_dates_to', '');
    \update_post_meta($post_id, '_price', $cost);
    \update_post_meta($post_id, '_sold_individually', '');
    \update_post_meta($post_id, '_manage_stock', 'no');
    \update_post_meta($post_id, '_backorders', 'no');
    \update_post_meta($post_id, '_stock', '');
    \update_post_meta($post_id, '_product_image_gallery', implode(",", $gallery));

    $desc = \apply_filters('the_content', $_POST[ 'description' ]);
    
    $form = new \stdClass();
    $form->state  = $this->req('state');
    $form->adress = $this->req('adress');
    $form->phone  = $this->req('phone');
    $form->hidephone = $this->req('hidephone', 0);
    $form->post_id = $post_id;

    /**
    * Update post meta, custom key 
    */
    \update_post_meta( $post_id, '_product_advert_state', $form->state );
    \update_post_meta( $post_id, '_product_advert_adress', $form->adress );
    \update_post_meta( $post_id, '_product_advert_phone', $form->phone );
    \update_post_meta( $post_id, '_product_advert_hidephone', $form->hidephone );

    $post = [
      'ID'     => $post_id,
      'post_status' => 'publish',
      'post_title'   => \esc_html((string)$_REQUEST[ 'title' ]),
      'post_content' => $desc,
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

      /* 
      * Set term product_cat to this post, 
      * if the term exist in product_cat taxonomy 
      */
      $categorie = (int) $_REQUEST[ 'categorie' ];
      $term = \term_exists( $categorie, 'product_cat');
      if (!is_null( $term )){
        $post_terms = \wp_set_post_terms( $current_post_id, [ $term[ 'term_id' ] ], 'product_cat');
        if (\is_wp_error( $post_terms )) 
          \wp_send_json( [
            'type' => false,
            'tracking' => 'Error: On set post terms ', 
            'data' => $post_terms->get_error_messages() 
            ] 
          );
      }

      /* Set attributes in the product post */
      if (isset($_REQUEST[ 'attributs' ])){
        $attributes = str_replace('\\"','"', trim($_REQUEST[ 'attributs' ]));
        $attributes = json_decode($attributes);
        $product_attributes = [];
        while (list(, $attribute) = each( $attributes )) {
          \wp_set_object_terms($current_post_id, $attribute->value, $attribute->_id);
          $product_attributes[ $attribute->_id ] = [
            'name' => $attribute->_id, // set attribute name
            'value' => (int)$attribute->value, // set attribute value
            'is_visible' => 1,
            'is_variation' => 0,
            'is_taxonomy' => 0 // !important
          ]; 
        }
        \update_post_meta($current_post_id, '_product_attributes', $product_attributes);
        \wp_send_json([
          'type' => true, 
          'data' => 'Update post with attributs',
          'redirect_url' => null
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
  }
  
  /* This is Lambda function to get REQUEST header content */
  public function req($k, $def=''){
    return isset( $_REQUEST[ $k ] ) ? $_REQUEST[ $k ] : $def;
  }
  
  public function action_register_user(){
    if (\is_user_logged_in())
      return false;
    
    if (!isset( $_REQUEST[ 'email' ], $_REQUEST[ 'password' ] ))
      return false;
    $user_id = \username_exists( \sanitize_title($_REQUEST[ 'lastname' ]) );
    if (isset( $_REQUEST[ 'lastname' ], $_REQUEST[ 'firstname' ] )) {
      if (!$user_id && \email_exists( $_REQUEST[ 'email' ] ) == false ) {
        if (isset( $_REQUEST[ 'password' ] ) && !empty($_REQUEST['password'])) {
          $user = \wp_create_user( \sanitize_title( trim($_REQUEST[ 'lastname' ]) ), $_REQUEST[ 'password' ], trim($_REQUEST[ 'email' ]) );
          if (!\is_wp_error($user)){
            $user_id = &$user; 
            /* Register success */
            $update_usr = \wp_update_user([
              'ID' => $user_id,
              'role' => 'subscriber'
            ]);
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
              'tracking' => 'Error: Create user.',
              'data' => $user->get_error_messages()
            ]);
          }
        } else {
          \wp_send_json(array(
            'type' => 'error', 
            'tracking' => 'Error: Please review $_REQUEST variable, `password` not send or not define. ',
            'data' => 'Request `password` is not defined.'
            )
          );
        };
        \wp_send_json(array('type'=>'success', 'data'=> $user_id));
      } else {
        \wp_send_json(array(
          'type' => 'error', 
          'tracking' => 'Error: Adress `email` or `user` already exists. ',
          'data' => 'User already exists.'
          )
        );
      }
    } else \wp_send_json([
      'type' => 'error',
      'tracking' => 'Error: Please review Request variable, `lastname` or `firstname` is not define.',
      'data' => 'There are variables not defined in the query.'
    ]);
  }
  
  public function action_delete_post(){
    if (!\is_user_logged_in())
      return false;
    
    if (!isset( $_REQUEST[ 'id' ] ))
      return false;

    $post_id = (int)trim($_REQUEST[ 'id' ]);
    $post_type = ( isset($_REQUEST['post_type']) && !empty(trim($_REQUEST['post_type'])) ) ? trim($_REQUEST['post_type']) : 'attachement';
    $args = [
      'p' => $post_id,
      'post_type' => $post_type
    ];
    $attachment = new \WP_Query( $args );
    if ($attachment->have_posts()) {
      \wp_send_json( \wp_delete_attachment( $post_id ) );
    } else {
      \wp_send_json( $attachment );
    }
      
    
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
    global $twig;
    $params = [
      'post_type' => 'page',
      'posts_per_page' => -1
    ];
    $posts = \get_posts( $params );
    $args = [
      'nonce' => \wp_nonce_field('advert_settings', 'advert_settings_nonce'),
      'register_page_id' => \get_option( 'register_page_id', false ),
      'addform_page_id' => \get_option( 'addform_page_id', false ),
      'posts' => $posts
    ];
    print $twig->render('@adminadvert/settings.html', $args);
  }
  
}


