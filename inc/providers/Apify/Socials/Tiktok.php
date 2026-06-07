<?php

namespace SocialsManager\Providers\Apify\Socials;

use SocialsManager\Providers\Apify\AbstractApifySocials as AbstractApifySocials;

if(!defined("ABSPATH")) exit;

class Tiktok extends AbstractApifySocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad Apify Tiktok Actor Format! try change the Actor", SM_SLUG);
  }

  public static function generate_body(string|array $url = null): array {

    $body = [
      'profiles'                      => is_array($url) ? $url : [$url],
      "resultsPerPage"                => 10,
      "commentsPerPost"               => 0,
      "excludePinnedPosts"            => false,
      "maxFollowersPerProfile"        => 0,
      "maxFollowingPerProfile"        => 0,
      "maxRepliesPerComment"          => 0,
      "scrapeRelatedVideos"           => false,
      "shouldDownloadAvatars"         => false,
      "shouldDownloadCovers"          => false,
      "shouldDownloadMusicCovers"     => false,
      "shouldDownloadSlideshowImages" => false,
      "shouldDownloadVideos"          => false,
      "topLevelCommentsPerPost"       => 0
    ];

    return apply_filters( 'sm_generate_apify_tiktok_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'nickName', 
      'biography'     => 'signature', 
      'members_count' => 'fans', 
    );

    return apply_filters('sm_tiktok_fields_map', $defaults);
  }

  public static function select_compatible_social(array $fetched_social): array {
    
    $fetched_social = $fetched_social[0]['authorMeta'] ?? throw new \RuntimeException(self::$error_message);
  
    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RuntimeException(self::$error_message);
    }
    
    return apply_filters( 'sm_filter_fetched_tiktok', $fetched_social );;
  }

  public static function get_engagement_rate(array $videos, ?int $members_count = null): int|float {

    $selected_social  = self::select_compatible_social($videos);
    $sample_video     = $videos[0];

    if( 
      !isset($sample_video['diggCount']) || !isset($sample_video['repostCount']) ||
      !isset($sample_video['shareCount']) || !isset($sample_video['playCount']) || 
      !isset($sample_video['collectCount']) || !isset($sample_video['commentCount'])
    ) {
      throw new \RuntimeException(__("failed to calculate engagement rate", SM_SLUG));
    }
    
    $total_engagements = 0;
    $valid_videos       = 0;

    if(empty($videos)) return 0;
    
    foreach($videos as $post) {

      $views = (intval($post['playCount']));

      if($views <= 0) {
        continue;
      }

      $current_engagement = 
        intval($post['diggCount'])           
        + (intval($post['commentCount']) * 3)  
        + (intval($post['shareCount']) * 5)    
        + (intval($post['collectCount']) * 4) 
        + (intval($post['repostCount']) * 6);

      $weighted_er = ($current_engagement / $views) * 100;
      $total_engagements   += $weighted_er;
      $valid_videos++;
    }

    if($valid_videos === 0) {
      return 0;
    }
    
    $engagement_rate = $total_engagements / $valid_videos;

    return apply_filters( 'sm_get_tiktok_engagement_rate', round($engagement_rate, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['avatar'] ?? throw new \RuntimeException(self::$error_message);
  }

}