<?php

namespace SocialsManager\Providers\Apify\Socials;

use SocialsManager\Providers\Apify\AbstractApifySocials as AbstractApifySocials;

if(!defined("ABSPATH")) exit;

class Instagram extends AbstractApifySocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad Apify Instagram Actor Format! try change the Actor", SM_SLUG);
  }

  public static function generate_body(string|array $username = null): array {
    $body = [
      'includeAboutSection' => false,
      'usernames'           => is_array($username) ? $username : [$username]
    ];

    return apply_filters( 'sm_generate_apify_instagram_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'fullName', 
      'biography'     => 'biography', 
      'members_count' => 'followersCount', 
    );

    return apply_filters('sm_instagram_fields_map', $defaults);
  }

  public static function select_compatible_social(array $fetched_social): array {
    
    $fetched_social = $fetched_social[0] ?? throw new \RuntimeException(self::$error_message);

    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RuntimeException(self::$error_message);
    }
    
    return apply_filters( 'sm_filter_fetched_instagram', $fetched_social );
  }

  public static function get_engagement_rate(array $fetched_social, ?int $members_count = null): int|float {

    $selected_social    = self::select_compatible_social($fetched_social);
    $followers_counts   = (int)   $selected_social['followersCount']  ?? 0;
    $recent_posts       = (array) $selected_social['latestPosts']     ?? [];
    $total_engagement   = 0;

    if(empty($followers_counts) || empty($recent_posts)) return 0;

    if(
      !isset($selected_social['latestPosts']) 
      || ( isset($selected_social['latestPosts']) && isset($recent_posts[0]) && ( !isset($recent_posts[0]['commentsCount']) || !isset($recent_posts[0]['likesCount']) ) )
    ) {
      throw new \RuntimeException(__("failed to calculate engagement rate", SM_SLUG));
    }

    foreach($recent_posts as $post) {
      $total_engagement += intval($post['commentsCount']);
      $total_engagement += intval($post['likesCount']);
    }

    $engagement_rate = (($total_engagement / count($recent_posts)) / $followers_counts) * 100;
    
    return apply_filters( 'sm_get_instagram_engagement_rate', round($engagement_rate, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['profilePicUrlHD'] ?? throw new \RuntimeException(self::$error_message);
  }

}