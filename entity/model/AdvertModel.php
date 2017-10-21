<?php
namespace advert\entity\model;
use advert\src\services as Services;

class AdvertModel {
  private $wpdb;

  public function __construct() {
    global $wpdb;
    $this->wpdb = &$wpdb;
    return;
  }

  public static function insert_term($name, $parent_term_id = 0, $taxonomy = 'product_cat') {
    return \wp_insert_term($name, $taxonomy, [ 'parent' => $parent_term_id ]);
  }

  public static function setProductCat() {
    $srvs = new Services\ServicesController();
    $SchemaAdvert = json_decode( $srvs->getSchemaAdvert() );
    $parents = $SchemaAdvert->product_cat;
    $childs = $SchemaAdvert->product_cat_child;
    $taxonomy = 'product_cat';

    foreach ($parents as $parent) {
      $verify = \term_exists( $parent->slug, $taxonomy ); // return array('term_id'=> x,'term_taxonomy_id'=>x))
      if (is_null( $verify )) {

        $isParent = \wp_insert_term($parent->name, $taxonomy,
          [ 'slug' => $parent->slug, 'parent' => 0 ]
        );
        if (!\is_wp_error( $isParent )) {
          $selfparent = \term_exists( $parent->slug, $taxonomy );
          $parent_term_id = $selfparent[ 'term_id' ];
          foreach ($childs as $child) {
            if ($parent->_id != $child->parent_id) continue;
            \wp_insert_term($child->name, $taxonomy, [ 'parent' => $parent_term_id ]);
          }
        }
      }
    }
  }

  public static function setDistricts() {
    $srvs = new Services\ServicesController();
    $districts = json_decode( $srvs->getSchemaDistricts() );
    $taxonomy = 'district';
    foreach ($districts as $district) {
      $verify = \term_exists( $district->name, $taxonomy);
      if (!is_null( $verify )) continue;
      $isInsert = \wp_insert_term( $district->name, $taxonomy, [
        'slug' => $district->code,
        'parent' => 0
      ]);
      return (!\is_wp_error( $isInsert )) ? true : false;
    }

  }

  /*
  * This function executed if after user register
  */
  public function add_user( $user_id ) {
    extract( $_REQUEST, EXTR_PREFIX_SAME, 'user');
    $Query = $this->wpdb->insert($this->wpdb->prefix."advert_user", array(
          'id_user'   => $user_id, // int
          'lastname'  => isset( $lastname ) ? esc_sql( $lastname ) : null ,
          'firstname' => esc_sql( $firstname ),
          'society'   => isset( $society ) ? esc_sql( $society ) : null ,
          'adress'       => isset( $adress ) ? esc_sql( $adress ) : null ,
          'postal_code'  => isset( $postal_code ) ? esc_sql( $postal_code ) : null ,
          'phone'     => isset( $phone ) ? (int)$phone : null, // int
          'SIRET'     => isset( $SIRET ) ? esc_sql( $SIRET ) : null 
        ),
          array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s'));
    if (!$Query) {
      return  $this->wpdb->print_error();
    } else {
      $this->wpdb->flush();
      $update_usr = \wp_update_user([
        'ID' => $user_id,
        'display_name' => isset($society) ? $society : $firstname
      ]);
      return \is_wp_error( $update_usr ) ? $update_usr->get_error_messages() : true;
    }
  }

  public function update_user($data = [], $where = []) {
    $Query = $this->wpdb->update("{$this->wpdb->prefix}advert_user", $data, $where);
    return (false === $Query) ? $this->wpdb->last_query : true;
  }

  public function get_advert_user( $user_id ) {
    $sql = $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->prefix}advert_user WHERE id_user = %d", $user_id );
    return $this->wpdb->get_row( $sql, OBJECT );
  }

  private static function create_role() {
    $result = \add_role(
      'advertiser',
      'Advertiser',
      array(
          'read'         => true,  // true allows this capability
          'upload_files' => true,
          'edit_posts'   => true,
          'edit_users'   => true,
          'manage_options' => true,
          'remove_users' => true,
          'edit_others_posts'   => true,
          'delete_others_pages'   => true,
          'delete_published_posts' => true,
          'edit_others_posts' => true, // Allows user to edit others posts not just their own
          'create_posts' => true, // Allows user to create new posts
          'manage_categories' => true, // Allows user to manage post categories
          'publish_posts' => true, // Allows the user to publish, otherwise posts stays in draft mode
          'edit_themes' => false, // false denies this capability. User can’t edit your theme
          'install_plugins' => false, // User cant add new plugins
          'update_plugin' => false, // User can’t update any plugins
          'update_core' => false // user cant perform core updatesy
      )
    );
    return (null != $result) ? true : false;
  }

  public static function deactivate() {
    \remove_role('advertiser');
  }

  public static function create_your_society() {
    if (\is_user_logged_in())
      return false;
    $User = \wp_get_current_user();
  }

  public static function create_advert_pages() {
    if (!is_admin()) return;
    $contents = [
      [ 'title' => 'Adverts', 'content' => '[adverts]' ],
      [ 'title' => 'Sing In', 'content' => '[singin_advert]' ],
      [ 'title' => 'Login', 'content' => '[login_advert]' ],
      [ 'title' => 'Add Listing', 'content' => '[addform_advert]' ],
      [ 'title' => 'Dashboard', 'content' => '[dashboard_advert]' ]
    ];

    /*
      \update_option( 'register_page_id', $register_page_id );
      \update_option( 'addform_page_id', $addform_page_id );
      \update_option( 'login_page_id', $login_page_id );
    */
    $user = \wp_get_current_user();
    while (list(, $content) = each( $contents )) {
      $page = \get_page_by_title( $content[ 'title' ] );
      if (\is_page( $page )) continue;
      $post_id = \wp_insert_post(array(
        'post_author' => $user->user_login,
        'post_title' => \wp_strip_all_tags( $content[ 'title' ]),
        'post_date' => date( 'Y-m-d H:i:s' ),
        'post_content' => $content[ 'content' ],
        'post_status' => 'publish', /* https://codex.wordpress.org/Post_Status */
        'post_parent' => '',
        'post_type' => "page",
      ));
      if (!\is_wp_error( $post_id )) {
        switch ($content[ 'title' ]) {
          case 'Sing In':
            update_option( 'register_page_id', $post_id );
            break;
          case 'Add Listing':
            update_option( 'addform_page_id', $post_id );
            break;
          case 'Login':
            update_option( 'login_page_id', $post_id );
            break;
          case 'Dashboard':
            update_option( 'dashboard_page_id', $post_id );
            break;
          case 'Adverts':
            update_option( 'adverts_page_id', $post_id );
            break;

          default:
            # code...
            break;
        }
        continue;
      } else {
        exit( $post_id->get_error_messages() );
      }
    }
  }

  public static function install() {
    global $wpdb;
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}advert_user" .
        "( id_advert_user BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT, " .
        "id_user BIGINT(20) UNSIGNED UNIQUE NOT NULL," .
        "lastname VARCHAR(250) NULL ," .
        "firstname VARCHAR(250) NOT NULL ," .
        "society VARCHAR(250) NULL ," .
        "SIRET VARCHAR(100) NULL ," .
        "adress VARCHAR(100) NULL ," .
        "postal_code VARCHAR(255) NULL ," .
        "phone INT(50) NULL ," .
        "add_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP );");

    $wpdb->query("ALTER TABLE {$wpdb->prefix}advert_user " .
        "ADD CONSTRAINT delete_custom_user " .
        "FOREIGN KEY (id_user) REFERENCES {$wpdb->prefix}users(ID) " .
        "ON DELETE CASCADE ON UPDATE NO ACTION;");

    namespace\AdvertModel::setProductCat();
    namespace\AdvertModel::setDistricts();
    namespace\AdvertModel::create_role();
    namespace\AdvertModel::create_advert_pages();
  }

  public static function uninstall(){
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}advert_user;");
  }
}
 ?>
