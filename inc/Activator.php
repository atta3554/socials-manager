<?php

namespace SocialsManager;

use SocialsManager\Utils\RouteUtils as RouteUtils;

if(!defined("ABSPATH")) exit;

class Activator {

  public function register($loader) {
    $loader->add_action('admin_notices', $this, 'add_admin_notices');
  }

  public function add_admin_notices(): void {
    $error = get_option(SM_ACTIVATION_ERROR);

    if (!$error) {
      return;
    }

    deactivate_plugins( plugin_basename(SM_FILE) );

    echo '<div class="notice notice-error is-dismissible">';
    echo '<p><strong>Socials Manager activation issue:</strong> ';
    echo esc_html($error);
    echo '</p>';
    echo '</div>';

    delete_option(SM_ACTIVATION_ERROR);
  }

  public static function activate(): void {
    try{
      RouteUtils::create_page(
        SM_USER_SOCIALS_DASHBOARD_PAGE_SLUG,
        SM_USER_SOCIALS_DASHBOARD_PAGE_TITLE,
        SM_USER_SOCIALS_DASHBOARD_PAGE_SHORTCODE,
        SM_USER_SOCIALS_DASHBOARD_PAGE_ID_OPTION,
      );

      RouteUtils::create_page(
        SM_USER_SOCIALS_PUBLIC_PAGE_SLUG,
        SM_USER_SOCIALS_PUBLIC_PAGE_TITLE,
        SM_USER_SOCIALS_PUBLIC_PAGE_SHORTCODE,
        SM_USER_SOCIALS_PUBLIC_PAGE_ID_OPTION,
      );

      $default_avatar_attachment_id = RouteUtils::create_attachment_from_local_files(SM_ASSETS_PATH . 'images/default-avatar.jpg');
      update_option(SM_DEFAULT_AVATAR_ATTACHMENT_ID_OPTION, $default_avatar_attachment_id);
    }
    catch (\Throwable $e) {
      update_option(SM_ACTIVATION_ERROR, $e->getMessage());
    }
    
    RewriteRules::add_rules();
    flush_rewrite_rules();
  }
}