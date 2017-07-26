<?php
class AdvertModel{
  private $wpdb;

  public function __construct(){
    global $wpdb;
    $this->wpdb = $wpdb;
  }

  private static function setProductCat(){
    $parent = array();
    // $parent[] = array('name' => 'EMPLOI', 'slug' => 'emploi');
    $parent[] = array('name' => 'VEHICULES', 'slug' => 'vehicules');
    $parent[] = array('name' => 'IMMOBILIER', 'slug' => 'immobilier');
    $parent[] = array('name' => 'VACANCES', 'slug' => 'vacances');
    $parent[] = array('name' => 'MULTIMEDIA', 'slug' => 'multimedia');
    $parent[] = array('name' => 'MAISON', 'slug' => 'maison');
    $parent[] = array('name' => 'LOISIRS', 'slug' => 'loisirs');
    $parent[] = array('name' => 'MATERIEL PROFESSIONNEL', 'slug' => 'materiel-professionnel');
    $parent[] = array('name' => 'SERVICES', 'slug' => 'services');

    foreach ($parent as $m) {
      $t = term_exists($m['slug'], 'product_cat'); // return array('term_id'=> x,'term_taxonomy_id'=>x))
      if(is_null($t)){
        wp_insert_term(
          $m['name'],
          'product_cat',
          array(
            'slug' => $m['slug'],
            'parent' => 0,
            'description' => ''
          )
        );
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




    AdvertModel::setProductCat();
  }

  public static function uninstall(){
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}advert;");
  }
}
 ?>
