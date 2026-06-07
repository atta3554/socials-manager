<?php

namespace SocialsManager\Providers\SerpApi\Socials;

use SocialsManager\Providers\SerpApi\AbstractSerpApiSocials as AbstractSerpApiSocials;

if(!defined("ABSPATH")) exit;

class Youtube extends AbstractSerpApiSocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad SerpApi Youtube API Format! try change the API", SM_SLUG);
  }

  public static function generate_body(string|array $user_url = null): array {

    $username = SocialUtils::get_valid_username('youtube', $user_url);

    $body = [
      'search_query'  => $username,
      'engine'      => 'youtube'
    ];

    return apply_filters( 'sm_generate_serapi_youtube_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'title', 
      'biography'     => 'description', 
      'members_count' => 'subscribers', 
    );

    return apply_filters('sm_youtube_fields_map', $defaults);
  }

  public static function select_compatible_social(array $fetched_info): array {
   
    $fetched_social = $fetched_info['channel_results'][0] ?? throw new \RuntimeException(self::$error_message);

    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RuntimeException(self::$error_message);
    }

    return apply_filters( 'sm_filter_fetched_youtube', $fetched_social );;
  }

  public static function get_engagement_rate(array $fetched_social, ?int $members_count = null): int|float {

    $calculation_failure_error = "failed to calculate engagement rate";

    if(!isset($fetched_social['video_results'])) {
      throw new \RuntimeException(__($calculation_failure_error, SM_SLUG));
    }

    $posts            = $fetched_social['video_results'];
    $channel_posts    = array_filter($posts, function($post) use ($fetched_social, $calculation_failure_error) {

      if(!isset($post['channel']) || !isset($post['channel']['name']) || !isset($post['views'])) {
        throw new \RuntimeException(__($calculation_failure_error, SM_SLUG));
      }

      return trim( strtolower($post['channel']['name']) ) === trim( strtolower($fetched_social['search_parameters']['search_query']) );

    });

    $total_views      = 0;
    $posts_count      = count($channel_posts);
    $followers        = (int) self::select_compatible_social($fetched_social)['subscribers'];
    
    if($posts_count === 0 || $followers === 0) {
      return 0;
    }

    foreach($channel_posts as $post) {
      $total_views += $post['views'] ?? 0;
    }

    $er_per_post  = $total_views / $posts_count;
    $er_rate      = ( $er_per_post / $followers ) * 100;
    
    return apply_filters( 'sm_get_youtube_engagement_rate', round($er_rate, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['thumbnail'] ?? throw new \RuntimeException(self::$error_message);
  }

}