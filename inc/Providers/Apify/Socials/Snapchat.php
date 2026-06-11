<?php

namespace SocialsManager\Providers\Apify\Socials;

if(!defined("ABSPATH")) exit;

class Snapchat extends \SocialsManager\Providers\Apify\AbstractApifySocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad Apify Snapchat Actor Format! try change the Actor", SM_SLUG);
  }

  public static function generate_body(string|array $url = null): array {

    $username = null;
    if(is_array($url)) {
      $username = array();
      foreach($url as $val) {
        $username[] = $val;
      }
    } else {
      $username = \SocialsManager\Utils\SocialUtils::get_valid_username('snapchat', $url);
    }

    $body = [
      'profilesInput' => is_array($username) ? $username : [$username]
    ];

    return apply_filters( 'sm_generate_apify_snapchat_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'username1', 
      'biography'     => 'profileDescription', 
      'members_count' => 'subscribers', 
    );

    return apply_filters('sm_snapchat_fields_map', $defaults);
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['profileImageUrl'] ?? throw new \RuntimeException(self::$error_message);
  }

  public static function select_compatible_social(array $fetched_social): array {
    
    $fetched_social = $fetched_social[0] ?? throw new \RuntimeException(self::$error_message);

    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RunetimeException(self::$error_message);
    }
    
    return apply_filters( 'sm_filter_fetched_snapchat', $fetched_social );
  }

  public static function get_engagement_rate(array $fetched_social, ?int $members_count = null): int|float {

    $selected_social  = self::select_compatible_social($fetched_social);    
    $followers_counts = (int)   $selected_social['subscribers']  ?? 0;
    $recent_posts     = (array) $selected_social['spotlights']   ?? [];
    $views_sum        = 0;

    if(empty($followers_counts || empty($recent_posts))) return 0;

    if(
      !isset($selected_social['spotlights']) || 
      (isset($selected_social['spotlights']) && !isset($recent_posts[0]['views'])) 
    ) {
      throw new \RuntimeException(__("failed to calculate engagement rate", SM_SLUG));
    }
        
    foreach($recent_posts as $post) {
      $views_sum += intval($post['views']);
    }
    
    $engagement_rate = ( ( $views_sum / count($recent_posts) ) / $followers_counts ) * 100;
    
    return apply_filters( 'sm_get_snapchat_engagement_rate', round($engagement_rate, 2) );
  }

}