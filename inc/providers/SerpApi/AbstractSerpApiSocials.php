<?php

namespace SocialsManager\Providers\SerpApi;

use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\APIUtils as APIUtils;

if(!defined("ABSPATH")) exit;

abstract class AbstractSerpApiSocials implements \SocialsManager\Providers\SocialInterface {
  
  protected string $base_url;
  protected string $token;

  abstract public static function get_fields_map(): array;

  public function __construct() {
    $this->base_url = 'https://serpapi.com/search';
    $this->token    = APIUtils::get_api_keys()['serpapi-key'] ?? '';
  }

  public function fetch(array $social_data): array {

    $user_social_url  = SocialUtils::get_user_social_url($social_data);

    if ( ! SocialUtils::get_valid_username($social_data['social_name'], $user_social_url) ) {
      throw new \InvalidArgumentException( __('Invalid ' . SocialUtils::get_label($social_data['social_name']) . ' URL.', SM_SLUG) );
    }

    $remote_url = $this->generate_url($user_social_url);

    return \SocialsManager\Services\HttpService::fetch($remote_url);
  }

  public static function validate_social(array $added_social, array $fetched_data): array {

    $fields_map       = static::get_fields_map();
    $selected_social  = static::select_compatible_social($fetched_data);
    $social_fields    = SocialUtils::filter_social_fields($selected_social, $fields_map);
    $final_array      = array_merge($social_fields, $added_social);
    $rate             = static::get_engagement_rate($fetched_data);
    $avatar_url       = static::get_avatar_url($selected_social);
    $attachment_id    = APIUtils::get_avatar($avatar_url, $final_array['name'], $final_array['social_name']);

    $final_array['avatar'] = $attachment_id;
    $final_array['engagement_rate'] = $rate;

    return $final_array;
  }

  public static function check_actor_compatibility(array $fetched_social): bool {
    
    $is_compatible = true;

    foreach(static::get_fields_map() as $local_field=>$remote_field) {
      if(!isset($fetched_social[$remote_field])) return false;
    }

    return $is_compatible;
  }

  public function generate_url(string $user_social_url): string {
    $data = static::generate_body($user_social_url);
    $data['api_key'] = $this->token;

    if(empty($data['api_key'])) {
      throw new \InvalidArgumentException("No SerpApi API Token found! Ask admins to add their token");
    }

    $remote_url = $this->base_url . '?' . http_build_query($data);
    return $remote_url;
  }
}