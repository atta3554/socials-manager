<?php

namespace SocialsManager\Providers\SerpApi\Socials;

use SocialsManager\Providers\SerpApi\AbstractSerpApiSocials as AbstractSerpApiSocials;
use \SocialsManager\Utils\SocialUtils as SocialUtils;

if(!defined("ABSPATH")) exit;

class Google extends AbstractSerpApiSocials{

  private static $error_message;

  public function __construct() {
    parent::__construct();
    self::$error_message = __("Bad SerpApi Google Map Contributor Reviews API Format! try change the API", SM_SLUG);
  }

  public static function generate_body(string|array $user_url = null): array {

    $contributor_id = SocialUtils::get_valid_username('google', $user_url);

    $body = [
      'contributor_id'  => $contributor_id,
      'engine'          => 'google_maps_contributor_reviews'
    ];

    return apply_filters( 'sm_generate_serapi_youtube_request_body', $body );
  }

  public static function get_fields_map(): array {
    $defaults = array(
      'name'          => 'name', 
      'biography'     => 'level', 
      'members_count' => 'points', 
    );

    return apply_filters('sm_youtube_fields_map', $defaults);
  }

  public static function select_compatible_social(array $fetched_info): array {
   
    $fetched_social = $fetched_info['contributor'] ?? throw new \RuntimeException(self::$error_message);

    if( !self::check_actor_compatibility($fetched_social) ) {
      throw new \RuntimeException(self::$error_message);
    }

    return apply_filters( 'sm_filter_fetched_youtube', $fetched_social );;
  }

  public static function get_engagement_rate(array $fetched_social, ?int $members_count = null): int|float {

    $fetched_social = self::select_compatible_social($fetched_data);

    $total_reviews_likes  = 0;
    $total_reviews        = (int) $fetched_social['contributions']['reviews'];
    
    if($total_reviews === 0) {
      return 0;
    }

    if(
      !isset($fetched_data['reviews']) || 
      ( isset($fetched_data['reviews']) && isset($fetched_data['reviews'][0]) && !isset($fetched_data['reviews'][0]['likes']) )
    ) {
      throw new \RuntimeException(__("failed to calculate engagement rate", SM_SLUG));
    }

    foreach($fetched_data['reviews'] as $review) {
      $total_reviews_likes += (int) $review['likes'];
    }

    $rate = $total_reviews_likes / $total_reviews;
    
    return apply_filters( 'sm_get_youtube_engagement_rate', round($rate, 2) );
  }

  public static function get_avatar_url(array $fetched_social): string {
    return $fetched_social['thumbnail'] ?? throw new \RuntimeException(self::$error_message);
  }

}