<?php

namespace SocialsManager\Providers\Official\Socials;

use \SocialsManager\Providers\Official\AbstractOfficialSocials as AbstractOfficialSocials;

if(!defined("ABSPATH")) exit;

class Instagram extends AbstractOfficialSocials {

  protected static $base_url = 'https://www.facebook.com/v23.0/dialog/oauth';
  protected static $token_url = 'https://graph.facebook.com/v20.0/oauth/access_token';
  protected static $label = 'instagram';

  public static function get_body_args(string $client_id, string $redirect_uri, string $state): array {
    return [
      'client_id'         => $client_id,
      "redirect_uri"      => $redirect_uri,
      'scope'             => 'instagram_basic,pages_show_list,instagram_manage_insights',
      'state'             => $state,
    ];
  }

  public static function get_token_request_body(string $code, array $instagram_official_data): array {
    return [
      'client_id'=> $instagram_official_data['client-id'],
      'client_secret'=> $instagram_official_data['client-secret'],
      'redirect_uri'  => $instagram_official_data['redirect-uri'],
      'code'          => $code
    ];
  }

  // public static function 

  private static function handle_oauth_callback(\WP_REST_Request $request): \WP_REST_Response {

    $token = parent::get_token($request);
    
    $user_pages = self::fb_get_pages($token);

    if(!is_array($user_pages) OR count($user_pages) === 0) {
      return ['error'=> 'failed to get pages'];
    }

    $igs = self::get_ig_accounts($user_pages);
    
    if(!$igs) {
      return ['error'=> 'no instagtam account is linked to any of your selected pages'];
    }
    
    $selected_ig = array_find($igs, fn ($ig) => $ig['username'] === $added_social);
    
    $recent_posts = self::fetch_recent_IG_posts($selected_ig, $user_token);
    
    if(!$recent_posts) {
      return ['error'=> 'failed to fetch recent posts'];
    }
    
    $engagement_rate = get_ig_er($recent_posts, $selected_ig['followers_count']);
    
    $updated_user_socials = update_socials($user_socials, $added_social, $selected_ig, $engagement_rate);
    
    $wr_post_meta['social'] = $updated_user_socials;
    
    update_post_meta($profile_id, 'wr_post_meta', $wr_post_meta);
    
    // delete_transient('ig_oauth_state_' . $uuid); ////////// activate for production
    
    wp_redirect($url);
    exit;
      
  }

  private static function fetch_recent_IG_posts($selected_ig, $user_token) {
      
    $medias_url = add_query_arg([
      "limit"=> "5",
      "access_token"=> $user_token,
      "fields"=> "comments_count,like_count"
    ], "https://graph.facebook.com/v23.0/" . $selected_ig['id'] . "/media");
    
    $medias_res = wp_remote_get($medias_url);

    if( is_wp_error($medias_res) ) {
      return "<div><h2>an error occured during fetch your IG datas</h2><h4>" . $media_res->get_error_message() . "</h4></div>";
    }
    
    $medias = json_decode(wp_remote_retrieve_body($medias_res), true);

    return $medias['data'] ?? NULL;
  }

  private static function get_ig_accounts($user_pages) {
    $igs = null;
    
    foreach($user_pages as $page) {
      if( isset( $page['instagram_business_account'] ) AND !empty( $page['instagram_business_account'] ) AND !empty( $page['instagram_business_account']['id'] ) ) {
        $ig = $page['instagram_business_account'];
        $igs[] = array(
          'id'                => $ig['id'],
          'username'          => $ig['username'],
          'biography'         => $ig['biography'],
          'followers_count'   => $ig['followers_count'],
          'name'              => $ig['name']
        );
      } 
    }
    
    return $igs;
  }

  private static function fb_get_pages($user_token) {
      
    $accounts_url = add_query_arg(array(
      'fields'=> 'name,instagram_business_account{id,name,username,biography,followers_count}',
      'access_token'=> $user_token
    ), "https://graph.facebook.com/v23.0/me/accounts");
    
    $acc_res = wp_remote_get($accounts_url, ['timeout'=> 10]);

    if(is_wp_error($acc_res)) {
      die("<div><h2>an error occured during receiving facebook page and related IG</h2><h4>" . $user_pages->get_error_message() . "</h4></div>");
    }
    
    $data = json_decode(wp_remote_retrieve_body($acc_res), true);

    return $data['data'] ?? NULL;
  }
}