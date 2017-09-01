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

  public static function insert_term($name, $parent_term_id = 0, $taxonomy = 'product_cat'){
    return \wp_insert_term($name, $taxonomy, ['parent' => $parent_term_id ]);
  }

  public static function setProductCat(){
    $Services = new Services\ServicesController();
    $SchemaAdvert = json_decode( $Services->getSchemaAdvert() );
    $parents = $SchemaAdvert->product_cat;
    $childs = $SchemaAdvert->product_cat_child;
    $taxonomy = 'product_cat';

    foreach ($parents as $parent) {
      $verify = \term_exists($parent->slug, $taxonomy); // return array('term_id'=> x,'term_taxonomy_id'=>x))
      if (is_null( $verify )){
        
        $isParent = \wp_insert_term($parent->name, $taxonomy, 
          [ 'slug' => $parent->slug, 'parent' => 0 ]
        );
        if (!\is_wp_error( $isParent )) { 
          $selfparent = \term_exists( $parent->slug, $taxonomy);
          $parent_term_id = $selfparent[ 'term_id' ];
          foreach ($childs as $child) {
            if ($parent->_id != $child->parent_id) continue;
            \wp_insert_term($child->name, $taxonomy, ['parent' => $parent_term_id ]);
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
        
    $wpdb->query("ALTER TABLE {$wpdb->prefix}advert_user " .
        "ADD CONSTRAINT delete_custom_user " .
        "FOREIGN KEY (id_user) REFERENCES {$wpdb->prefix}users(ID) " .
        "ON DELETE CASCADE ON UPDATE NO ACTION;");
    
    namespace\AdvertModel::setProductCat();
  }

  public static function uninstall(){
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}advert_user;");
  }
}
 ?>
