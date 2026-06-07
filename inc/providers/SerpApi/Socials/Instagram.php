<?php

namespace SocialsManager\Providers\SerpApi\Socials;

use SocialsManager\Providers\SerpApi\AbstractSerpApiSocials as AbstractSerpApiSocials;

if(!defined("ABSPATH")) exit;

class Instagram extends AbstractSerpApiSocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad SerpApi Instagram API Format! try change the API", SM_SLUG);
  }

  public static function generate_body(string|array $user_url = null): array {

    $username = SocialUtils::get_valid_username('instagram', $user_url);

    $body = [
      'profile_id'  => $username,
      'engine'      => 'instagram_profile'
    ];

    return apply_filters( 'sm_generate_serapi_youtube_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'full_name', 
      'biography'     => 'biography', 
      'members_count' => 'followers', 
    );

    return apply_filters('sm_youtube_fields_map', $defaults);
  }

  public static function select_compatible_social(array $fetched_info): array {
   
    $fetched_social = $fetched_info['profile_results'] ?? throw new \RuntimeException(self::$error_message);

    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RuntimeException(self::$error_message);
    }

    return apply_filters( 'sm_filter_fetched_youtube', $fetched_social );;
  }

  public static function get_engagement_rate(array $fetched_social, ?int $members_count = null): int|float {

    $fetched_social = self::select_compatible_social($fetched_social);

    $total_engagement = 0;
    $posts            = $fetched_social['posts'];
    $posts_count      = count($posts);
    $followers        = (int) $fetched_social['followers'];
    
    if($posts_count === 0 || $followers === 0) {
      return 0;
    }

    if(!isset($posts[0]['liked_by_count']) || !isset($posts[0]['comments_count'])) {
      throw new \RuntimeException(__("failed to calculate engagement rate", SM_SLUG));
    }

    foreach($posts as $post) {
      $total_engagement += $post['liked_by_count'] ?? 0;
      $total_engagement += $post['comments_count'] ?? 0;
    }

    $er_per_post  = $total_engagement / $posts_count;
    $rate         = ( $er_per_post / $followers ) * 100;
    
    return apply_filters( 'sm_get_youtube_engagement_rate', round($rate, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['profile_pic_url_hd'] ?? throw new \RuntimeException(self::$error_message);
  }

}