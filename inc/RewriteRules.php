<?php 

namespace SocialsManager;

if(!defined("ABSPATH")) exit;

class RewriteRules {
  function register($loader) {
    $loader->add_action('init', $this, 'add_rules');
  }

  public static function add_rules() {
    add_rewrite_rule( '^socials-public/([^/]+)/?$', 'index.php?pagename=' . SM_USER_SOCIALS_PUBLIC_PAGE_SLUG . '&sm_username=$matches[1]', 'top' );
    add_rewrite_tag('%sm_username%', '([^&]+)');
  }
}