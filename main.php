<?php

if(!defined('ABSPATH')) exit();

/**
 * Plugin Name: Social manager
 * Description: Theme-independent social account manager and validator.
 * Author: Elmira Ashrafi
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Text Domain: socials-manager
 */


// Define constants 
define("SM_VERSION", "1.0.0");
define("SM_FILE", __FILE__);
define("SM_PATH", plugin_dir_path( SM_FILE ));
define("SM_URL", plugin_dir_url( SM_FILE ));
define("SM_INC", SM_PATH . 'inc/');
define("SM_TMP", SM_PATH . 'templates/');
define("SM_ASSETS_PATH", SM_PATH . 'assets/');
define("SM_ASSETS_URL", SM_URL . 'assets/');
define("SM_SLUG", "socials-manager");
define("SM_NONCE_NAME", 'sm_nonce');
define("SM_USER_META_KEY", 'sm_user_socials');
define("SM_ACTIVATION_ERROR", 'sm_activation_error');
define("SM_REST_ROUTE_NAMESPACE", 'socials-manager/v1/');
define("SM_REST_ROUTE_PATH", 'oauth/callback/');

// API Providers and socials datas
define("SM_API_KEYS_OPTION", "sm_api_keys");
define("SM_APIFY_ACTOR_IDS_OPTION", "sm_actor_ids");
define("SM_OFFICIAL_DATAS_OPTION", 'sm_official_datas');
define("SM_SELECTED_SOCIALS_OPTION", "sm_selected_social");
define("SM_SELECTED_API_PROVIDER_OPTION", "sm_selected_api_provider");
define("SM_ALLOWED_SOCIALS_COUNT_OPTION", 'sm_allowed_socials_count');
define("SM_DEFAULT_AVATAR_ATTACHMENT_ID_OPTION", 'sm_default_avatar_attachment_id');

// Pages datas
define("SM_SUBMIT_SOCIALS_FORM_SHORTCODE", "sm_submit_socials_form");
define("SM_USER_SOCIALS_PUBLIC_PAGE_ID_OPTION", "sm_user_socials_public_page_id");
define("SM_USER_SOCIALS_PUBLIC_PAGE_SHORTCODE", 'sm_user_socials_public_page');
define("SM_USER_SOCIALS_PUBLIC_PAGE_SLUG", 'socials-public');
define("SM_USER_SOCIALS_PUBLIC_PAGE_TITLE", 'Socials Public');
define("SM_USER_SOCIALS_DASHBOARD_PAGE_ID_OPTION", "sm_user_socials_dashboard_page_id");
define("SM_USER_SOCIALS_DASHBOARD_PAGE_SHORTCODE", 'sm_user_socials_dashboard_page');
define("SM_USER_SOCIALS_DASHBOARD_PAGE_SLUG", 'socials-dashboard');
define("SM_USER_SOCIALS_DASHBOARD_PAGE_TITLE", 'Socials Dashboard');
define("SM_USER_SOCIALS_ARCHIVE_PUBLIC_PAGE_SHORTCODE", 'sm_archive_social_owners');
define("SM_USER_SOCIALS_SINGLE_PUBLIC_PAGE_SHORTCODE", 'sm_social_owners_socials');

// autoloader
spl_autoload_register(function ($class) {
  $prefix = 'SocialsManager\\';
  $base_dir = SM_INC;

  if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
      return;
  }

  $relative_class = substr($class, strlen($prefix));

  $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  if (file_exists($file)) {
      require_once $file;
  }
});


// register activation hook
register_activation_hook(__FILE__, [SocialsManager\Activator::class, 'activate']);


// run the plugin 
$plugin = SocialsManager\Plugin::instance();
$plugin->run();
