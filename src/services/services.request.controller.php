<?php
namespace advert\src\services;

final class ServicesRequestHttp {

  /* This is Lambda function to get REQUEST header content */
  public static function req($k, $def = false){
    $request = trim($_REQUEST[ $k ]);
    return isset( $request ) ? ( !empty( $request ) ? $request : false ) : $def;
  }
}
 ?>
