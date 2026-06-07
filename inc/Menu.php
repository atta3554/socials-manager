<?php 

namespace SocialsManager;

use SocialsManager\Utils\UserUtils as UserUtils;
use SocialsManager\Utils\RouteUtils as RouteUtils;
use SocialsManager\Utils\APIUtils as APIUtils;
use SocialsManager\Utils\SocialUtils as SocialUtils;

if(!defined('ABSPATH')) exit();

class Menu {
  public function register($loader): void {
    $loader->add_action('admin_menu', $this, 'init_admin');
    $loader->add_action("admin_post_save_provider", $this, 'save_api_settings');
    $loader->add_action("wp_ajax_social_validation", $this, 'validate_socials');
  }

  public function init_admin(): void {
    add_menu_page( esc_html__( 'Socials manager', SM_SLUG ), esc_html__( 'Socials manager', SM_SLUG ), 'manage_options', SM_SLUG, false);
    add_submenu_page( SM_SLUG, esc_html__( "API settings", SM_SLUG ), esc_html__( "API settings", SM_SLUG ), 'manage_options', SM_SLUG, array($this, 'load_api_page'));
    add_submenu_page( SM_SLUG, esc_html__( "Checkup", SM_SLUG ), esc_html__( "Checkup", SM_SLUG ), 'manage_options', SM_SLUG . '-checkup', array($this, 'load_checkup_page'));
    add_submenu_page( SM_SLUG, esc_html__( "Shortcodes", SM_SLUG ), esc_html__( "Shortcodes", SM_SLUG ), 'manage_options', SM_SLUG . '-shortcodes', array($this, 'load_shortcodes_page'));
  }

  public function load_api_page(): void {
    echo RouteUtils::get_sm_template( 'sm_get_admin_api_settings_page', SM_TMP . 'admin/admin-api.php');
  }

  public function load_checkup_page(): void {

    $users = get_users([
      'meta_key'      => SM_USER_META_KEY,
      'meta_compare'  => 'EXISTS'
    ]);

    $socials = \SocialsManager\Utils\SocialUtils::get_allowed_socials();

    require SM_TMP . 'admin/admin-checkup.php';
  }

  public function load_shortcodes_page(): void {

    $shortcode_maps = [
      'User socials dashboard page' => [
        'value'       => SM_USER_SOCIALS_DASHBOARD_PAGE_SHORTCODE,
        'description' => __("this shortcode renders content of user socials dashboard. social owners can see their added socials and edit or delete them from this page", SM_SLUG)
      ],
      'User socials Public page'    => [
        'value'       => SM_USER_SOCIALS_SINGLE_PUBLIC_PAGE_SHORTCODE,
        'description' => __("this shortcode renders content of user socials public page. this is the profile page of social owner which others see.", SM_SLUG)
      ],
      'public socials handler'      => [
        'value'       => SM_USER_SOCIALS_PUBLIC_PAGE_SHORTCODE,
        'description' => __(sprintf("this shortcode renders content of global socials public page. it is combination of %s and %s, which shows archive social owners if don't pass username, and shows user socials public page if username supplied", SM_USER_SOCIALS_ARCHIVE_PUBLIC_PAGE_SHORTCODE, SM_USER_SOCIALS_SINGLE_PUBLIC_PAGE_SHORTCODE), SM_SLUG)
      ],
      'archive socials owners'      => [
        'value'       => SM_USER_SOCIALS_ARCHIVE_PUBLIC_PAGE_SHORTCODE,
        'description' => __("this shortcode renders content of archive social owners page. every user which has added at least one social will render here", SM_SLUG)
      ],
      'submit social form'          => [
        'value'       => SM_SUBMIT_SOCIALS_FORM_SHORTCODE,
        'description' => __("this shortcode renders content of social submission form.", SM_SLUG)
      ],
    ];

    $shortcode_maps = apply_filters( 'sm_shortcodes', $shortcode_maps );

    require SM_TMP . 'admin/admin-shortcodes.php';
  }

  public function save_api_settings(): void {

    check_admin_referer( 'sm_save_provider', SM_NONCE_NAME );

    $selected_providers     = isset($_POST['providers'])              ? UserUtils::sanitize_array( $_POST['providers'] )          : [];
    $actor_ids              = isset($_POST['actor_ids'])              ? UserUtils::sanitize_array( $_POST['actor_ids'] )          : [];
    $official_datas         = isset($_POST['official_datas'])         ? UserUtils::sanitize_array( $_POST['official_datas'] )     : [];
    $selected_socials       = isset($_POST['socials'])                ? UserUtils::sanitize_array( $_POST['socials'] )            : [];
    $api_keys               = isset($_POST['keys'])                   ? UserUtils::sanitize_array( $_POST['keys'] )               : [];
    $allowed_socials_count  = isset($_POST['allowed_socials_count'])  ? sanitize_text_field( $_POST['allowed_socials_count'] )    : '';

    \SocialsManager\Providers\Apify\ApifyUtils::save_actor_ids($actor_ids);
    \SocialsManager\Providers\Official\OfficialUtils::save_officials_datas($official_datas);

    APIUtils::save_selected_api_providers($selected_providers);
    APIUtils::save_api_keys($api_keys);
    SocialUtils::save_selected_socials($selected_socials);
    SocialUtils::update_user_allowed_socials_count($allowed_socials_count);
    
    RouteUtils::redirect_back(['success'=> 'true']);
  }

  public function validate_socials(): void {
    check_ajax_referer( 'checkup_nonce', SM_NONCE_NAME );

    $social_name  = isset($_POST['social'])   && !empty($_POST['social'])   ? sanitize_text_field( $_POST['social'] )   : '';
    $user         = isset($_POST['user-id'])  && !empty($_POST['user-id'])  ? absint( $_POST['user-id'] )               : '';

    

    wp_send_json_success( 'success' );
  }
}