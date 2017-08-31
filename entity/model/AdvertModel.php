<?php
namespace advert\entity\model;
use advert\src\services as Services;

class AdvertModel{
  private $wpdb;

  public function __construct(){
    global $wpdb;
    $this->wpdb = $wpdb;
    return;
  }

  public static function setProductCat(){
    $Services = new Services\ServicesController();
    $SchemaAdvert = json_decode( $Services->getSchemaAdvert() );
    $parents = $SchemaAdvert->product_cat;
    $childs = $SchemaAdvert->product_cat_child;
    foreach ($parents as $parent) {
      $verify = \term_exists($parent->slug, 'product_cat'); // return array('term_id'=> x,'term_taxonomy_id'=>x))
      if (is_null( $verify )){
        
        $isParent = \wp_insert_term($parent->name, 'product_cat', 
          [ 'slug' => $parent->slug, 'parent' => 0 ]
        );
        if (!\is_wp_error( $isParent )) { 
          $selfparent = \term_exists( $parent->slug, 'product_cat');
          $parent_term_id = $selfparent[ 'term_id' ];
          foreach ($childs as $child) {
            if ($parent->_id != $child->parent_id) return;
            $objChild = (object) $child;
            \wp_insert_term($objChild->name, 'product_cat', 
              [
                'parent' => $parent_term_id
              ]
            );
      
          }
        }
      }
    }
  }

  public function setAdvert($form){ // $form is Object Stdclass
    $Query = $this->wpdb->insert($this->wpdb->prefix."advert", array(
            'post_id'     => $form->post_id,
            'state'       => $form->state,
            'phone' =>       $form->phone,
            'hidephone' =>  $form->hidephone,
            'adress'      => $form->adress),
            array('%d', '%s', '%s', '%d', '%s'));
    if(!$Query){
      return  $this->wpdb->print_error();
    } else{  return true;  }
  }

  public static function install(){
    global $wpdb;
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}advert"
        . "(id_advert BIGINT(20) UNSIGNED PRIMARY KEY,"
        ."id_advert_user BIGINT(20) UNSIGNED UNIQUE NOT NULL ,"
        ."post_id BIGINT(20) UNSIGNED UNIQUE NOT NULL,"
        ."state VARCHAR(50) NOT NULL,"
        ."adress VARCHAR(250) NOT NULL,"
        ."phone INT(10) NOT NULL,"
        ."hidephone TINYINT(1) NOT NULL DEFAULT '1',"
        ."add_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
        .");");
    $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}advert_user" .
        "( id_advert_user BIGINT(20) UNSIGNED PRIMARY KEY, " .
        "id_user BIGINT(20) UNSIGNED UNIQUE NOT NULL," .
        "lastname VARCHAR(250) NOT NULL ," .
        "firstname VARCHAR(250) NOT NULL ," .
        "society VARCHAR(250) NOT NULL ," .
        "SIRET VARCHAR(100) NOT NULL ," .
        "adress VARCHAR(100) NOT NULL ," .
        "postal_code VARCHAR(255) NOT NULL ," .
        "phone INT(50) NOT NULL ," .
        "rubric VARCHAR(255) NOT NULL ," .
        "add_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP);");

    $wpdb->query("ALTER TABLE {$wpdb->prefix}advert " .
        "ADD CONSTRAINT delete_user " .
        "FOREIGN KEY (id_advert_user) REFERENCES {$wpdb->prefix}advert_user(id_advert_user) " .
        "ON DELETE CASCADE ON UPDATE NO ACTION;");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}advert " .
        "ADD CONSTRAINT delete_post " .
        "FOREIGN KEY (post_id) REFERENCES {$wpdb->prefix}posts(ID) " .
        "ON DELETE CASCADE ON UPDATE NO ACTION;");
    $wpdb->query("ALTER TABLE {$wpdb->prefix}advert_user " .
        "ADD CONSTRAINT delete_custom_user " .
        "FOREIGN KEY (id_user) REFERENCES {$wpdb->prefix}users(ID) " .
        "ON DELETE CASCADE ON UPDATE NO ACTION;");
    
    namespace\AdvertModel::setProductCat();
  }

  public static function uninstall(){
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}advert;");
  }
}
 ?>
