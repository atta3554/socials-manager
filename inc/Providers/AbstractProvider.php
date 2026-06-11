<?php

namespace SocialsManager\Providers;

use SocialsManager\Providers\ProviderInterface as ProviderInterface;
use SocialsManager\Providers\SocialInterface as SocialInterface;

use SocialsManager\Utils\SocialUtils as SocialUtils;

abstract class AbstractProvider implements ProviderInterface {

  abstract public static function get_provider_socials(): array;

  public static function get_label(): string {
    return static::$label;
  }

  public function validate_request(array $social_data, int $user_id, string $error_title): void {

    $social_name  = $social_data['social_name'] ?? '';
    $url          = $social_data[$social_name . '-url'] ?? '';
    $desc         = $social_data['description'] ?? '';

    if(empty($user_id) || empty($social_data) || empty($social_name) || empty($url)) {
      wp_send_json_error( ['message'=> __('invalid data', SM_SLUG), 'title'=> $error_title], 400 );
    }

    if(!\SocialsManager\Utils\UserUtils::current_user_can_edit($user_id)) {
     wp_send_json_error( ['message'=> __('UnAuthorized User', SM_SLUG), 'title'=> $error_title], 403 ); 
    }

    if(!SocialUtils::is_social_allowed($social_name)) {
      wp_send_json_error( ['message'=> __('Unallowed social', SM_SLUG), 'title'=> $error_title], 400 );
    }

    if(SocialUtils::social_exists($social_data, $user_id)) {
      wp_send_json_error( ['message'=> __('Social exists', SM_SLUG), 'title'=> $error_title], 400 );
    }

    if(SocialUtils::get_user_allowed_socials_count() === 'single_social' && SocialUtils::has_account_on_platform($social_name, $user_id)) {
      wp_send_json_error( ['message'=> __('Only one account on each platform is allowed!', SM_SLUG), 'title'=> $error_title], 400 );
    }
  }

  public function create_social_instance(array $social_data): SocialInterface  {

    $user_social        = $social_data['social_name'];
    $supported_socials  = static::get_provider_socials();
    
    if(empty($supported_socials[$user_social]) || ! class_exists($supported_socials[$user_social])) {
      throw new \RuntimeException(sprintf(__('%1$s is not supported in this provider. Try another provider for %1$s', SM_SLUG), $user_social));
    }

    $instance = new $supported_socials[$user_social]($social_data);

    if(! $instance instanceof SocialInterface) {
      throw new \RuntimeException(__('invalid social class', SM_SLUG));
    }

    return $instance;
  }

  public static function get_front_data_receiver_template(array $social): string {
    return \SocialsManager\Utils\RouteUtils::get_sm_template(
      'sm_get_social_url_form_group', 
      SM_TMP . 'front/sections/submit-socials-form/social-url-form-group.php',
      ['social'=> $social]
    );
  }
}