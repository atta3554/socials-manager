<?php 

namespace SocialsManager;

use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\UserUtils as UserUtils;
use SocialsManager\Utils\RouteUtils as RouteUtils;
use SocialsManager\Services\HttpService as HttpService;
use SocialsManager\Services\SocialsService as SocialsService;

if(!defined('ABSPATH')) exit();

class Shortcodes {

  public function register($loader) {
    $loader->add_action('init'                      , $this, 'register_shortcodes'              );
    $loader->add_action('wp_ajax_sm_submit_social'  , $this, 'submit_social'                    );
    $loader->add_action('wp_ajax_sm_remove_social'  , $this, 'remove_social'                    );
    $loader->add_action('wp_ajax_sm_edit_social'    , $this, 'edit_social'                      );
  }

  public function register_shortcodes() {
    add_shortcode( SM_USER_SOCIALS_DASHBOARD_PAGE_SHORTCODE         , [$this, 'render_user_socials_dashboard'       ] );
    add_shortcode( SM_USER_SOCIALS_PUBLIC_PAGE_SHORTCODE            , [$this, 'render_user_socials_public_page'     ] );
    add_shortcode( SM_USER_SOCIALS_ARCHIVE_PUBLIC_PAGE_SHORTCODE    , [$this, 'render_archive_socials_public_page'  ] );
    add_shortcode( SM_USER_SOCIALS_SINGLE_PUBLIC_PAGE_SHORTCODE     , [$this, 'render_single_socials_public_page'   ] );
    add_shortcode( SM_SUBMIT_SOCIALS_FORM_SHORTCODE                 , [$this, 'render_submit_socials_form'          ] );
  }

  public function render_user_socials_public_page() {
    
    $username = get_query_var( 'sm_username' );

    if(!$username && isset($_GET['username'])) {
      $username = sanitize_user( wp_unslash( $_GET['username'] ) );
    }

    if(!$username) {
      return self::render_archive_socials_public_page();
    }

    return self::render_single_socials_public_page($username);
  }

  public function render_user_socials_dashboard() {
    
    if(!is_user_logged_in()) return self::get_message('non_logged_in');

    $user_id      = get_current_user_id();
    $user_socials = SocialUtils::get_user_socials($user_id);
    $form_mode    = 'edition';

    if(empty($user_socials)) return self::get_message('socials_not_found');

    wp_enqueue_style(  'sm-dashboard-styles'  );
    wp_enqueue_style(  'sm-form-styles'       );
    wp_enqueue_script( 'sm-dashboard-scripts' );
    
    $args             = array(
      'user_id'       => $user_id,
      'form_mode'     => $form_mode,
      'user_socials'  => $user_socials,
    );

    return RouteUtils::get_sm_template('sm_get_user_socials_dashboard', SM_TMP . 'front/user-socials-dashboard.php', $args);
  }

  public function render_submit_socials_form() {

    if(!is_user_logged_in()) return self::get_message('non_logged_in');

    wp_enqueue_style(  'sm-form-styles'  );
    wp_enqueue_script( 'sm-form-scripts' );

    $user_id          = get_current_user_id();
    $allowed_socials  = SocialUtils::get_allowed_socials();
    $form_mode        = 'submission';
    $args             = array(
      'user_id'         => $user_id,
      'form_mode'       => $form_mode,
      'allowed_socials' => $allowed_socials,
    );
    
    return RouteUtils::get_sm_template( 'sm_get_submit_socials_form', SM_TMP . 'front/submit-socials-form.php', $args );
  }

  public function edit_social() {
    check_ajax_referer( 'sm_edit_social', SM_NONCE_NAME );

    $error_title  = __("Error", SM_SLUG);
    $user_id      = isset($_POST['user_id']) ? absint( $_POST['user_id'] ) : 0;
    $social_data  = isset($_POST['social']) && is_array($_POST['social']) && count($_POST['social']) > 0 ? UserUtils::sanitize_user_social( $_POST['social'] ) : [];

    $editing_social = SocialUtils::social_exists($social_data, $user_id);

    if(!$editing_social) {
      wp_send_json_success( ['title'=> $error_title, 'message'=> __('Social not found', SM_SLUG)], 400 );
    }

    try {

      $provider_instance  = \SocialsManager\Services\ProviderFactory::create_provider_instance($social_data['social_name']);
      $social_instance    = $provider_instance->create_social_instance($social_data);
      $social_response    = $social_instance->fetch($social_data, $user_id);

      if(!$social_response['success']) {
        wp_send_json_error( 
          [
            'message'=> $social_response['message'] ?? __('failed to fetch social data', SM_SLUG), 
            'title'=> $error_title
          ], 
          $social_response['code'] ?? 500
        );
      }

      $new_social         = $social_instance->validate_social($social_data, $social_response['body']);
      $remaining_socials  = SocialUtils::remove_user_social($user_id, $editing_social);
      SocialUtils::update_user_socials($user_id, [...$remaining_socials, $new_social]);

    } catch (\Exception $e) {
      $error_code = $e instanceof \InvalidArgumentException ? 400 : 500;
      wp_send_json_error([
        'message' => __($e->getMessage(), SM_SLUG),
        'title'=> $error_title
      ], 500);
    } 

    wp_send_json_success([
      'title'   => __('success', SM_SLUG), 
      'message' => __('social edited successfully', SM_SLUG),
      'url'     => get_permalink( \SocialsManager\Utils\RouteUtils::get_dashboard_page_id() )
    ]);
  }

  public function remove_social() {
    check_ajax_referer( 'sm_remove_social', SM_NONCE_NAME );

    $user_id      = isset($_POST['user']) ? absint( $_POST['user'] ) : 0;
    $social_data  = isset($_POST['social']) && is_array($_POST['social']) && count($_POST['social']) > 0 ? UserUtils::sanitize_user_social( $_POST['social'] ) : [];

    $deleting_social = SocialUtils::social_exists($social_data, $user_id);

    if(!$deleting_social) {
      wp_send_json_success( ['title'=> __('Error', SM_SLUG), 'message'=> __('Social not found', SM_SLUG)], 400 );
    }

    $remaining_socials = SocialUtils::remove_user_social($user_id, $deleting_social);

    SocialUtils::update_user_socials($user_id, $remaining_socials);

    wp_send_json_success( ['title'=> __('success', SM_SLUG), 'message'=> __('social removed successfully', SM_SLUG)] );
  }

  public function submit_social() {
    check_ajax_referer( 'sm_submit_social', SM_NONCE_NAME );
  
    $error_title  = __("Error", SM_SLUG);
    $user_id      = isset($_POST['user_id']) ? absint( $_POST['user_id'] ) : 0;
    $social_data  = isset($_POST['social']) && is_array($_POST['social']) && count($_POST['social']) > 0 ? UserUtils::sanitize_user_social( $_POST['social'] ) : [];
    $social_name  = $social_data['social_name'] ?? '';

    if(empty($social_name) || empty($user_id)) {
      wp_send_json_error( ['message'=> __('invalid data', SM_SLUG), 'title'=> $error_title], 400 );
    }

    $provider_instance  = \SocialsManager\Services\ProviderFactory::create_provider_instance($social_name);
    $provider_instance->validate_request($social_data, $user_id, $error_title);

    try {

      $social_instance  = $provider_instance->create_social_instance($social_data);
      $social_response  = $social_instance->fetch($social_data, $user_id);

      if(!$social_response['success']) {
        wp_send_json_error( 
          [
            'message'=> $social_response['message'] ?? __('failed to fetch social data', SM_SLUG), 
            'title'=> $error_title
          ], 
          $social_response['code'] ?? 500
        );
      }

      $new_social = $social_instance->validate_social($social_data, $social_response['body']);
      SocialUtils::add_user_socials($user_id, $new_social);

    }  catch (\Exception $e) {
      $error_code = $e instanceof \InvalidArgumentException ? 400 : 500;
      wp_send_json_error([
        'message' => __($e->getMessage(), SM_SLUG),
        'title'=> $error_title
      ], $error_code);
    } 

    wp_send_json_success([
      'message'=> __("Social created Successfully!", SM_SLUG), 
      'title'=> __('success', SM_SLUG), 
      'url'=> get_permalink( \SocialsManager\Utils\RouteUtils::get_dashboard_page_id() )
    ]);
  }

  private static function render_archive_socials_public_page(): string {

    $users = get_users([
      'meta_key'      => SM_USER_META_KEY,
      'meta_compare'  => 'EXISTS'
    ]);

    if(empty($users)) return self::get_message('no_users_found');

    $args = [
      'users'=> $users
    ];

    return RouteUtils::get_sm_template('sm_get_archive_users_page', SM_TMP . 'front/archive-users-page.php', $args);
  }

  private static function render_single_socials_public_page(string $username): string {
    $user = get_user_by( 'login', $username );

    if(!$user) return self::get_message('user_not_found');

    $user_socials = SocialUtils::get_user_socials($user->ID);

    if(empty($user_socials)) return self::get_message('socials_not_found');

    wp_enqueue_style(  'sm-public-styles'  );
    wp_enqueue_script( 'sm-public-scripts' );

    $args = [
      'user_id'       => $user->ID,
      'user_socials'  => $user_socials
    ];

    return RouteUtils::get_sm_template('sm_get_user_socials_public_page', SM_TMP . 'front/user-socials-public-page.php', $args);
  }

  private static function get_message(string $type): string {

    $messages = [
      'non_logged_in' => __('You should login first, in order to manage your socials', SM_SLUG),
      'user_not_found' => __('User not found!', SM_SLUG),
      'no_users_found' => __('No user found with active social account!', SM_SLUG),
      'socials_not_found' => __('Socials not found! try to add some socials first', SM_SLUG),
    ];

    $message = $messages[$type] ?? __('Unexpected error.', SM_SLUG);

    return sprintf(
      '<h2 style="text-align:center;">%s</h2>',
      esc_html($message)
    );
    
  }
} 