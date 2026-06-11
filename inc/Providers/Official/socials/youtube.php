<?php

namespace SocialsManager\Providers\Official\Socials;

use \SocialsManager\Providers\Official\AbstractOfficialSocials as AbstractOfficialSocials;

if(!defined("ABSPATH")) exit;

class Youtube extends AbstractOfficialSocials{

  protected static $oauth_code_base_url   = 'https://accounts.google.com/o/oauth2/v2/auth';
  protected static $access_token_base_url = 'https://oauth2.googleapis.com/token';
  protected static $social_data_base_url  = 'https://www.googleapis.com/youtube/v3/';
  protected static $social_label          = 'youtube';

  public static function get_oauth_body_args(string $client_id, string $redirect_uri, string $state): array {
    return [
      'client_id'     => $client_id,
      "redirect_uri"  => $redirect_uri,
      'scope'         => 'https://www.googleapis.com/auth/youtube.readonly',
      "response_type" => 'code',
      'state'         => $state,
      "access_type"   => 'offline',
      'prompt'        => 'consent select_account'
    ];
  }

  public static function handle_oauth_callback(\WP_REST_Request $request): \WP_REST_Response {

    try {
      $fetched_data                     = parent::validate_oauth_code($request);
      $access_token                     = parent::get_access_token($fetched_data['oauth_code'], $fetched_data['social_name']);
      $account_data                     = self::get_account($access_token);
      $social_data                      = self::select_compatible_social($account_data);
      $final_social                     = array_merge($fetched_data, $social_data);
      $channel_videos                   = self::get_videos($account_data, $access_token);
      $video_ids_str                    = self::extract_video_ids($channel_videos);
      $statistics_datas                 = self::get_videos_statistics($video_ids_str, $access_token);
      $channel_er                       = self::get_engagement_rate($statistics_datas, (int) $social_data['members_count']);
      $final_social['avatar']           = \SocialsManager\Utils\APIUtils::get_avatar($final_social['avatar'], $social_data['name'], $fetched_data['social_name']);
      $final_social['engagement_rate']  = $channel_er;
      $final_social['youtube-url']      = 'https://youtube.com/' . $account_data['items'][0]['snippet']['customUrl'];
    }

    catch(\Throwable $e) {
      
      $error_code = $e instanceof \InvalidArgumentException ? 400 : 500;

      return new \WP_REST_Response([
        'message' => __($e->getMessage(), SM_SLUG)
      ], $error_code);

    }

    $user_id = $final_social['user_id'];
    unset($final_social['user_id']);
    unset($final_social['oauth_code']);

    \SocialsManager\Utils\SocialUtils::add_user_socials((int) $user_id, $final_social);

    return new \WP_REST_Response(null, 302, [
      'location'=> home_url( SM_USER_SOCIALS_DASHBOARD_PAGE_SLUG )
    ]);
  }

  public static function get_token_request_body(string $code, array $youtube_official_datas): array {
    return [
      'code'          => $code,
      'client_id'     => $youtube_official_datas['client-id'] ?? '',
      'client_secret' => $youtube_official_datas['client-secret'] ?? '',
      'redirect_uri'  => $youtube_official_datas['redirect-uri'] ?? '',
      'grant_type'    => 'authorization_code',
    ];
  }

  private static function get_account(string $token): array {
    $account_data_url = self::$social_data_base_url . 'channels?' . http_build_query(self::get_data_request_body());

    $account_response = wp_remote_get($account_data_url, [
      'headers'=> ['Authorization' => 'Bearer ' . $token]
    ]);

    if (is_wp_error($account_response)) {
      throw new \RuntimeException($account_response->get_error_message());
    }

    return json_decode(wp_remote_retrieve_body($account_response), true);
  }

  public static function get_data_request_body(): array {
    return [
      'part'  =>  'snippet,statistics,contentDetails',
      'mine'  =>  'true',
    ];
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => ["snippet", "title"], 
      'biography'     => ["snippet", "description"], 
      'members_count' => ["statistics", "subscriberCount"], 
      'avatar'        => ["snippet", "thumbnails", 'default', 'url'], 
    );

    return apply_filters('sm_youtube_fields_map', $defaults);
  }

  public static function select_compatible_social(array $account_data): array {
   
    $fetched_social = $account_data['items'][0] ?? throw new \RuntimeException(self::$error_message);
    $data           = [];

    foreach(self::get_fields_map() as $local_field=>$remote_field) {
      $data[$local_field] = self::array_get($fetched_social, $remote_field);
    }

    return apply_filters( 'sm_filter_fetched_youtube', $data );;
  }

  public static function get_videos(array $account_data, string $access_token): array {
    $upload_play_list_id = $account_data['items'][0]['contentDetails']['relatedPlaylists']['uploads'] ?? '';
    
    if(!$upload_play_list_id) {
      throw new \RuntimeException(__('Failed to get channel videos.', SM_SLUG));
    }

    $videos_url = self::$social_data_base_url . 'playlistItems?' . http_build_query([
      'part'       => 'snippet,contentDetails',
      'playlistId' => $upload_play_list_id,
      'maxResults' => 10,
    ]);

    $videos_response = wp_remote_get($videos_url, [
      'headers'=> [
        'Authorization' => 'Bearer ' . $access_token,
        'Accept'        => 'application/json'
        ]
    ]);

    if(is_wp_error( $videos_response )) {
      throw new \RuntimeException($videos_response->get_error_message());
    }

    return json_decode(wp_remote_retrieve_body($videos_response), true);
  }

  private static function extract_video_ids(array $channel_videos): string {
    
    if(empty($channel_videos)) {
      throw new \RuntimeException(__('Failed to get channel videos.', SM_SLUG));
    }
  
    $video_ids = [];

    foreach($channel_videos['items'] as $video) {
      $video_ids[] = $video['contentDetails']['videoId'];
    }

    $video_ids = implode(',', $video_ids);

    return $video_ids;
  }

  private static function get_videos_statistics(string $video_ids, string $access_token): array {
    
    $videos_url       = self::$social_data_base_url . 'videos?part=statistics&id=' . $video_ids;

    $videos_response  = wp_remote_get($videos_url, [
      'headers'=> ['Authorization' => 'Bearer ' . $access_token]
    ]);

    if(is_wp_error( $videos_response )) {
      throw new \RuntimeException($videos_response->get_error_message());
    }

    return json_decode(wp_remote_retrieve_body($videos_response), true);
  }

  public static function get_engagement_rate(array $fetched_social, ?int $subscribers_count = null): int|float {
    $videos_statistics = $statistics_datas['items'];
    $total_er = 0;
    
    foreach($videos_statistics as $video) {
      $video_statistics = $video['statistics'];

      $total_er += $video_statistics['viewCount'];
      $total_er += $video_statistics['likeCount'];
      $total_er += $video_statistics['commentCount'];
      $total_er += $video_statistics['favoriteCount'];
      $total_er += $video_statistics['dislikeCount'];
    }
    
    $views_average = ($total_er / count($videos_statistics)) / $subscribers_count;
    
    return apply_filters( 'sm_get_youtube_engagement_rate', round($views_average, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['channelAvatarUrl'] ?? throw new \RuntimeException(self::$error_message);
  }

  private static function array_get(array $data, array $path, mixed $default = null): string|null {

    if(!is_array($data)) {
      return $default;
    }

    foreach($path as $key) {
      if(!array_key_exists($key, $data)) {
        return $default;
      }
      $data = $data[$key];
    }

    return $data;

  }

}
