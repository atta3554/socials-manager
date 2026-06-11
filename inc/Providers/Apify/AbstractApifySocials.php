<?php

namespace SocialsManager\Providers\Apify;

use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\APIUtils as APIUtils;

if(!defined("ABSPATH")) exit;

abstract class AbstractApifySocials implements \SocialsManager\Providers\SocialInterface {
  
  protected string $base_url;
  protected string $token;

  abstract public static function get_fields_map(): array;

  public function __construct() {
    $this->base_url = 'https://api.apify.com/v2/acts';
    $this->token    = APIUtils::get_api_keys()['apify-key'] ?? '';

    if(empty($this->token)) {
      throw new \InvalidArgumentException("No Apify API Token found! Ask admins to add token");
    }
  }

  public function fetch(array $social_data, int $user_id): array {

    $user_social_url = SocialUtils::get_user_social_url($social_data);
    $social_actor_id = \SocialsManager\Providers\Apify\ApifyUtils::get_single_actor_id($social_data['social_name']);

    if ( ! SocialUtils::get_valid_username($social_data['social_name'], $user_social_url) ) {
      throw new \InvalidArgumentException( __('Invalid ' . SocialUtils::get_label($social_data['social_name']) . ' URL.', SM_SLUG) );
    }

    $social_actor_url = $this->base_url . '/' . $social_actor_id . '/run-sync-get-dataset-items';
    $request_args     = $this->generate_args($user_social_url);

    return \SocialsManager\Services\HttpService::fetch($social_actor_url, $request_args);
  }

  public static function validate_social(array $added_social, array $fetched_data): array {

    $fields_map       = static::get_fields_map();
    $selected_social  = static::select_compatible_social($fetched_data);
    $social_fields    = SocialUtils::filter_social_fields($selected_social, $fields_map);
    $final_array      = array_merge($social_fields, $added_social);
    $views_average    = static::get_engagement_rate($fetched_data);
    $avatar_url       = static::get_avatar_url($selected_social);
    $attachment_id    = APIUtils::get_avatar($avatar_url, $final_array['name'], $final_array['social_name']);

    $final_array['avatar'] = $attachment_id;
    $final_array['engagement_rate'] = $views_average;
    
    return $final_array;
  }

  public static function check_actor_compatibility(array $fetched_social): bool {
    
    $is_compatible = true;

    foreach(static::get_fields_map() as $local_field=>$remote_field) {
      if(!isset($fetched_social[$remote_field])) return false;
    }

    return $is_compatible;
  }

  public function generate_args(string|array $user_social_url): array {
    return [
      "method"  => "POST",
      "timeout" => 20,
      'headers' => $this->generate_headers(),
      'body'    => wp_json_encode($this->generate_body($user_social_url))
    ];
  }

  public function generate_headers(): array {
    return [
      "Content-Type"  => "application/json",
      "Authorization" => "Bearer " . $this->token
    ];
  }
}