<?php 

namespace SocialsManager\Utils;

if(!defined("ABSPATH")) exit;

class SocialUtils {

  public static function get_supported_socials(): array {

    $defaults = array(
      array('social_name' => 'instagram', 'social_label' => __('Instagram', SM_SLUG)    , 'label_for_url' => __('Instagram Profile URL', SM_SLUG) , 'placeholder_for_url' => 'https://www.instagram.com/username'      , 'members_name'=> __('followers',   SM_SLUG) , 'engagement_rate_label'=> __('Engagement Rate', SM_SLUG)  , 'validation_pattern' => '#^(https?:\/\/)?(www\.)?instagram\.com\/([^\/\?\#]+)\/?$#'),
      array('social_name' => 'youtube'  , 'social_label' => __('YouTube', SM_SLUG)      , 'label_for_url' => __('Youtube Channel URL', SM_SLUG)   , 'placeholder_for_url' => 'https://www.youtube.com/@channelname'    , 'members_name'=> __('subscribers', SM_SLUG) , 'engagement_rate_label'=> __('View Rate',       SM_SLUG)  , 'validation_pattern' => '#^(https?:\/\/)?(www\.)?youtube\.com\/@([^\/\?\#]+)\/?$#'),
      array('social_name' => 'tiktok'   , 'social_label' => __('TikTok', SM_SLUG)       , 'label_for_url' => __('TikTok Profile URL', SM_SLUG)    , 'placeholder_for_url' => 'https://www.tiktok.com/@username'        , 'members_name'=> __('followers',   SM_SLUG) , 'engagement_rate_label'=> __('Engagement Rate', SM_SLUG)  , 'validation_pattern' => '#^(https?:\/\/)?(www\.)?tiktok\.com\/@([^\/\?\#]+)\/?$#'),
      array('social_name' => 'snapchat' , 'social_label' => __('Snapchat', SM_SLUG)     , 'label_for_url' => __('Snapchat Profile URL', SM_SLUG)  , 'placeholder_for_url' => 'https://www.snapchat.com/@username'      , 'members_name'=> __('followers',   SM_SLUG) , 'engagement_rate_label'=> __('View Rate',       SM_SLUG)  , 'validation_pattern' => '#^(https?:\/\/)?(www\.)?snapchat\.com\/@([^\/\?\#]+)\/?$#'),
      array('social_name' => 'google'   , 'social_label' => __('Google Maps', SM_SLUG)  , 'label_for_url' => __('Google Maps URL', SM_SLUG)       , 'placeholder_for_url' => 'https://www.google.com/maps/contrib/...' , 'members_name'=> __('reviewers',   SM_SLUG) , 'engagement_rate_label'=> __('Like Rate',       SM_SLUG)  , 'validation_pattern' => '#^(https?:\/\/)?(www\.)?google\.com\/maps\/contrib\/([^\/\?\#]+)\/?$#')
    );

    return apply_filters('sm_supported_socials', $defaults);
  }

  public static function get_selected_socials(): array {
    return get_option( SM_SELECTED_SOCIALS_OPTION, [] );
  }

  public static function save_selected_socials(array $selected_socials): bool {
    return update_option( SM_SELECTED_SOCIALS_OPTION , array_keys($selected_socials));
  }

  public static function get_user_allowed_socials_count(): string {
    return get_option( SM_ALLOWED_SOCIALS_COUNT_OPTION, 'multiple_social');
  }

  public static function update_user_allowed_socials_count(string $value): bool {
    return update_option( SM_ALLOWED_SOCIALS_COUNT_OPTION, $value );
  }

  public static function get_allowed_socials(): array {
    
    $supported_socials = self::get_supported_socials();
    $selected_socials = self::get_selected_socials();

    $allowed_socials = array_filter($supported_socials, fn($supported_social) => in_array($supported_social['social_name'], $selected_socials, true));

    return array_values($allowed_socials);
  }

  public static function get_user_socials(int $user_id): array {
    $socials = get_user_meta($user_id, SM_USER_META_KEY, true);
    return is_array($socials) ? $socials : array();
  }

  public static function add_user_socials(int $user_id, array $added_social): int|bool {
    $user_socials   = get_user_meta( $user_id, SM_USER_META_KEY, true );
    $user_socials   = is_array($user_socials) ? $user_socials : [];
    $user_socials[] = $added_social;
    return update_user_meta($user_id, SM_USER_META_KEY, $user_socials);
  }

  public static function update_user_socials(int $user_id, array $user_socials): int|bool {
    return update_user_meta( $user_id, SM_USER_META_KEY, $user_socials );
  }

  public static function is_social_allowed(string $social): bool {
    return in_array($social, array_column(self::get_allowed_socials(), 'social_name'), true);
  }

  public static function find_supported_social(string $social_name): ?array {
    foreach (self::get_supported_socials() as $social) {
      if (($social['social_name'] ?? '') === $social_name) {
        return $social;
      }
    }

    return null;
  }

  public static function extend_social(array $social): array {
    if(empty($social['social_name'])) {
      throw new \InvalidArgumentException(__('Unsupported social.', SM_SLUG));
    }

    $global_social_data = self::find_supported_social($social['social_name']);

    if(!$global_social_data) {
      throw new \InvalidArgumentException(__('Unsupported social.', SM_SLUG));
    }

    return array_merge($global_social_data, $social);
  }

  public static function social_exists(array $social_data, int $user_id): bool|array {
    $user_socials         = self::get_user_socials($user_id);
    $submited_social_url  = self::get_user_social_url($social_data);

    foreach($user_socials as $social) {
      $current_social_url = self::get_user_social_url($social);
      if( trim(strtolower($submited_social_url)) === trim(strtolower($current_social_url)) ) {
        return $social;
      }
    }

    return false;
  }

  public static function has_account_on_platform(string $platform, int $user_id): bool {
    $user_socials = self::get_user_socials($user_id);
    foreach($user_socials as $social) {
      if($social['social_name'] === $platform) {
        return true;
      }
    }

    return false;
  }

  public static function remove_user_social(int $user_id, array $searching_social): array {
    $user_socials       = self::get_user_socials($user_id);
    $remaining_socials  = array_filter($user_socials, function($social) use ($searching_social) {
      $current_social_url   = self::get_user_social_url($social);
      $searching_social_url = self::get_user_social_url($searching_social);
      return $current_social_url !== $searching_social_url;
    });
    
    return array_values($remaining_socials);
  }

  public static function get_label(string $social_name): string {
    $social = self::find_supported_social($social_name);
  
    if(!$social) {
      throw new \InvalidArgumentException(__('Unsupported social.', SM_SLUG));
    }

    return $social['social_label'];
  } 

  public static function filter_social_fields(array $fetched_social_data, array $required_fields): array {
    foreach($required_fields as $local_field=>$remote_field) {
      $required_fields[$local_field] = $fetched_social_data[$remote_field] ?? throw new \RuntimeException("Bad Apify instagram Actor Format! try change the Actor");
    }
    return $required_fields;    
  }

  public static function get_validation_pattern(string $social_name): string {
    foreach(self::get_supported_socials() as $social) {
      if($social['social_name'] === $social_name) {
        return $social['validation_pattern'] ?? throw new \InvalidArgumentException(__('Validation pattern not defined.', SM_SLUG));
      }
    }
    throw new \InvalidArgumentException(__('Unsupported social.', SM_SLUG));
  }

  public static function filter_allowed_socials(array $socials): array {
    return array_intersect_key($socials, array_flip(array_column(self::get_allowed_socials(), 'social_name')));
  }

  public static function get_user_social_url(array $social_data): string {
    if(empty($social_data['social_name'])) {
      throw new \InvalidArgumentException(__('Unsupported social.', SM_SLUG));
    }

    $social_name  = sanitize_key( $social_data['social_name'] );
    $url_key      = sanitize_key($social_name . '-url');
    return $social_data[$url_key] ?? throw new \InvalidArgumentException(__($social_data['social_name'] . " URL Not Found", SM_SLUG));
  }

  public static function get_valid_username(string $social_name, string $social_url): string {
    $pattern = SocialUtils::get_validation_pattern($social_name);
    preg_match($pattern, $social_url, $matches);
    return $matches[3] ?? throw new \InvalidArgumentException(__("Failed to get username from url", SM_SLUG));
  }
}
