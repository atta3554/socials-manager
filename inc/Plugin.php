<?php 

namespace SocialsManager;

if(!defined('ABSPATH')) exit();

class Plugin {

  private static $instance = null;
  public $loader;
  public $menus;
  public $assets;
  public $shortcodes;
  public $activator;
  public $rewrite_rules;
  public $http_service;

  public static function instance() {
    if(self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function __construct() {
    $this->load_dependencies();
    $this->define_services();
    $this->register_hooks();
  }

  public function load_dependencies() {
    $this->loader = new Loader();
  }

  public function define_services() {
    $this->menus          = new Menu();
    $this->assets         = new Assets();
    $this->shortcodes     = new Shortcodes();
    $this->activator      = new Activator();
    $this->rewrite_rules  = new RewriteRules();
    $this->http_service   = new \SocialsManager\Services\HttpService();
  }

  public function register_hooks() {
    $this->menus->register($this->loader);
    $this->assets->register($this->loader);
    $this->shortcodes->register($this->loader);
    $this->activator->register($this->loader);
    $this->rewrite_rules->register($this->loader);
    $this->http_service->register($this->loader);
  }

  public function run() {
    $this->loader->run();
  }
}