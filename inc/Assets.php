<?php 

namespace SocialsManager;

use SocialsManager\Utils\RouteUtils as RouteUtils;

if(!defined('ABSPATH')) exit();

class Assets {
  public function register($loader) {
    $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_assets');
    $loader->add_action('wp_enqueue_scripts', $this, 'enqueue_front_assets');
  }

  public function enqueue_admin_assets() {

    if(RouteUtils::is_admin_shortcodes_page()) {
      wp_enqueue_style('sm-admin-shortcodes-styles'  , SM_ASSETS_URL . 'admin/styles/admin-shortcodes.css', [], SM_VERSION);
    }

    if(RouteUtils::is_admin_checkup_page()) {
      wp_enqueue_style('sm-admin-checkup-styles'    , SM_ASSETS_URL . 'admin/styles/admin-checkup.css',     [], SM_VERSION);
      wp_enqueue_script('sm-admin-checkup-scripts'  , SM_ASSETS_URL . 'admin/scripts/admin-checkup.js', ['jquery'], SM_VERSION, true);
    }

    if(RouteUtils::is_admin_api_page()) {
      wp_enqueue_style('sm-admin-api-styles'        , SM_ASSETS_URL . 'admin/styles/admin-api.css',         [], SM_VERSION);
      wp_enqueue_script('admin-api-scripts'         , SM_ASSETS_URL . 'admin/scripts/admin-api.js', ['jquery'], SM_VERSION, true);
    }
  }

  public function enqueue_front_assets() {
    
    wp_register_style('sm-form-styles'          , SM_ASSETS_URL . 'front/styles/form.css'               , [                                   ], SM_VERSION);
    wp_register_style('sm-dashboard-styles'     , SM_ASSETS_URL . 'front/styles/dashboard.css'          , ['sm-form-styles'                   ], SM_VERSION);
    wp_register_style('sm-public-styles'        , SM_ASSETS_URL . 'front/styles/public.css'             , [                                   ], SM_VERSION);
    
    wp_register_script('sm-form-scripts'        , SM_ASSETS_URL . 'front/scripts/form.js'               , ['jquery', 'sm-helpers'             ], SM_VERSION, true);
    wp_register_script('sm-dashboard-scripts'   , SM_ASSETS_URL . 'front/scripts/dashboard.js'          , ['jquery', 'sm-helpers'             ], SM_VERSION, true);
    wp_register_script('sm-public-scripts'      , SM_ASSETS_URL . 'front/scripts/public.js'             , ['jquery', 'sm-three'               ], SM_VERSION, true);
    
    wp_enqueue_script( 'sm-sweet-alert'         , SM_ASSETS_URL . 'front/scripts/sweetalert.min.js'     , ['jquery',                          ], SM_VERSION, true );
    wp_enqueue_script( 'sm-helpers'             , SM_ASSETS_URL . 'front/scripts/helpers.js'            , ['jquery', 'sm-sweet-alert'         ], SM_VERSION, true );
    
    wp_register_script('sm-three'               , SM_ASSETS_URL . 'front/scripts/three.min.js'          , ['jquery'                           ], SM_VERSION, true);
    // wp_register_script('sm-social-3d'           , SM_ASSETS_URL . 'front/scripts/social-3d.js'          , ['jquery', 'sm-three'               ], SM_VERSION, true);

    wp_localize_script( 'sm-form-scripts', 'ajax_data', [
      'ajax_url'=> admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce( 'sm_submit_social' )
    ]);

    wp_localize_script( 'sm-dashboard-scripts', 'SM_DATA', [
      'ajax_url'            => admin_url('admin-ajax.php'),
      'remove_nonce'        => wp_create_nonce( 'sm_remove_social' ),
      'nonce_field'         => SM_NONCE_NAME,
      
      'i18n'=> [
        'social_not_found_title'              => __("Error", SM_SLUG),
        'social_not_found_message'            => __("failed to found social!", SM_SLUG),
        'remove_social_confirmation_title'    => __("Are you sure?", SM_SLUG),
        'remove_social_confirmation_message'  => __("Social will remove from your dashboard and profile", SM_SLUG),
        'confirm_remove_button_text'          => __("Confirm", SM_SLUG),
        'cancel_remove_button_text'           => __("Cancel", SM_SLUG),
      ]
    ]);
  }
}