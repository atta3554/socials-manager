<?php

namespace SocialsManager\Providers\Apify\Socials;

use SocialsManager\Providers\Apify\AbstractApifySocials as AbstractApifySocials;

if(!defined("ABSPATH")) exit;

class Youtube extends AbstractApifySocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad Apify Youtube API Format! try change the API", SM_SLUG);
  }

  public static function generate_body(string|array $user_url = null): array {
    
    $start_urls = [];

    if(is_array($user_url)) {
      foreach($user_url as $url) {
        $start_urls[] = ['url'=> $url];
      }
    } else {
      $start_urls[] = ['url'=> $user_url];
    }
  
    $body = [
      'startUrls'         => $start_urls,
      "maxResultStreams"  => 0,
      "maxResults"        => 10,
      "maxResultsShorts"  => 0,
    ];

    return apply_filters( 'sm_generate_apify_youtube_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'channelUsername', 
      'biography'     => 'channelDescription', 
      'members_count' => 'numberOfSubscribers', 
    );

    return apply_filters('sm_youtube_fields_map', $defaults);
  }

  public static function select_compatible_social(array $fetched_videos): array {
   
    $fetched_social = $fetched_videos[0]['aboutChannelInfo'] ?? throw new \RuntimeException(self::$error_message);

    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RuntimeException(self::$error_message);
    }

    return apply_filters( 'sm_filter_fetched_youtube', $fetched_social );;
  }

  public static function get_engagement_rate(array $videos, ?int $members_count = null): int|float {
    $total_views = 0;
    
    foreach($videos as $video) {
      $total_views += $video['viewCount'];
    }
    
    $views_average = ($total_views / count($videos)) / $videos[0]['numberOfSubscribers'];
    
    return apply_filters( 'sm_get_youtube_engagement_rate', round($views_average, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['channelAvatarUrl'] ?? throw new \RuntimeException(self::$error_message);
  }

}