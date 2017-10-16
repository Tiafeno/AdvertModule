<?php
namespace advert\src\factory;

final class Factory {
  protected $Nonce = null;
  public $fielName = null;
  public function __construct( $fieldName ) {
    $this->fieldName = &$fieldName;
    return $this->setNonce();
  }
  public function setNonce() {
    $this->Nonce = \wp_create_nonce( $this->fieldName );
    return $this;
  }
  public function getNonce() {
    return $this->Nonce;
  }
  public function verifyNonce() {

  }
}
