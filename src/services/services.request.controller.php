<?php
namespace advert\src\services;

final class Request {

  /* This is Lambda function to get REQUEST header content */
  public static function req($k, $def = false){
    $request = trim($_REQUEST[ $k ]);
    return isset( $request ) ? ( !empty( $request ) ? $request : false ) : $def;
  }

  /* This function return SESSION variable */
  public static function getSession( $name, $def = false) {
    if (!isset($_SESSION[ $name ]) || empty($_SESSION[ $name ]))
      return $def;
    return trim($_SESSION[ $name ]);
  }

  /* This function return POST or GET value by `name` variable */
  public static function getValue($name, $def = false) {
    if (!isset( $name ) || empty( $name ) || !is_string( $name ))
      return $def;
    $returnValue = isset($_POST[ $name ]) ? trim( $_POST[ $name ] ) : (isset($_GET[ $name ]) ? trim( $_GET[ $name ] ) : $def);
    $returnValue = urldecode( preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode( $returnValue )) );
    return !is_string( $returnValue ) ? $returnValue : stripslashes( $returnValue );
  }
}
 ?>
