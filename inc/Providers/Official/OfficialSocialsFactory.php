<?php

namespace SocialsManager\Providers\Official;

use \SocialsManager\Utils\SocialUtils as SocialUtils;

if(!defined("ABSPATH")) exit;

class OfficialSocialsFactory extends \SocialsManager\Providers\AbstractProvider {

  protected static $label = 'Official';

  public static function get_provider_socials(): array {
    
    $official_supported_socials = array(
      'youtube'   => \SocialsManager\Providers\Official\Socials\Youtube::class,
      'instagram' => \SocialsManager\Providers\Official\Socials\Instagram::class,
    );
  
    $official_filtered_socials = apply_filters( 'sm_official_socials', $official_supported_socials );

    return \SocialsManager\Utils\SocialUtils::filter_allowed_socials($official_filtered_socials);
  }

  public function validate_request(array $social_data, int $user_id, string $error_title): void {

    $social_name  = $social_data['social_name'] ?? '';
    $desc         = $social_data['description'] ?? '';

    if(empty($user_id) || empty($social_data) || empty($social_name)) {
      wp_send_json_error( ['message'=> __('invalid data', SM_SLUG), 'title'=> $error_title], 400 );
    }

    if(!\SocialsManager\Utils\UserUtils::current_user_can_edit($user_id)) {
     wp_send_json_error( ['message'=> __('UnAuthorized User', SM_SLUG), 'title'=> $error_title], 403 ); 
    }

    if(!SocialUtils::is_social_allowed($social_name)) {
      wp_send_json_error( ['message'=> __('Unallowed social', SM_SLUG), 'title'=> $error_title], 400 );
    }

    if(SocialUtils::get_user_allowed_socials_count() === 'single_social' && SocialUtils::has_account_on_platform($social_name, $user_id)) {
      wp_send_json_error( ['message'=> __('Only one account on each platform is allowed!', SM_SLUG), 'title'=> $error_title], 400 );
    }
  }

  public static function get_provider_api_settings_fields_template_path(): string {
    return SM_TMP . 'admin/providers/official/';
  }

    public static function get_front_data_receiver_template(array $social): string {
      $social_name = esc_html($social['social_name']);
    return "<fieldset class='$social_name'><h3 style='margin-bottom: 0px;'>" . __("No any additional details needed! Just click submit social button.", SM_SLUG) .'</h3></fieldset>';
  }
}