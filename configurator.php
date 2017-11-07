<?php
namespace advert\configurator;

class Configurator {
  public $env;
  public $folder;
  public $application;
  public function __construct( $conf ) {
    $this->env = &$conf;
    $this->folder = ($this->env == 'prod') ? 'dist' : 'js';
    
  }
  public function get( $fileName, $type = 'app' ) {
    $app = explode('.', $fileName);
    $this->application = ($this->env == 'dev' && $type == 'app') ? $app[ 0 ] . '/' : '';
    $withMin = $this->env == 'prod' ? '.min.js' : '.js';
    return "/assets/{$this->folder}/{$type}/{$this->application}" . $fileName . $withMin;
  } 
}
