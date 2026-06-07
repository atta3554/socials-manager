<?php

namespace SocialsManager\Providers\Official;

use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\APIUtils as APIUtils;

if (!defined("ABSPATH")) {
  exit;
}

abstract class AbstractOfficialSocials implements \SocialsManager\Providers\SocialInterface{
  
  protected string $token;

  abstract public static function get_fields_map(): array;
  abstract public static function get_oauth_body_args(string $client_id, string $redirect_uri, string $state): array;
  abstract public static function handle_oauth_callback(\WP_REST_Request $request): \WP_REST_Response;

  public function fetch(array $social_data, int $user_id): array {
    $oauth_url = $this->generate_oauh_url();
    
    wp_send_json_success([
      'title'   => __("Success", SM_SLUG),
      'message' => __('Close this to redirect to google account selection page', SM_SLUG),
      'url'     => $oauth_url
    ]);
  }

  public static function validate_oauth_code(\WP_REST_Request $request): array {
    $error          = $request->get_param('error');
    $code           = $request->get_param('code');
    $state          = $request->get_param('state');
    $error_message  = __( "Invalid data", SM_SLUG );
    
    if($error) {
      throw new \InvalidArgumentException($error);
    }
    
    if(!$code) {
      throw new \InvalidArgumentException($error_message);
    }
    
    if(!$state) {
      throw new \InvalidArgumentException($error_message);
    }
    
    $state_key  = 'sm_oauth_state_' . static::$social_label . '_' . $state;
    $state_data = get_transient( $state_key );
    
    if(!$state_data) {
      throw new \InvalidArgumentException($error_message);
    }

    delete_transient( $state_key );

    $state_data['oauth_code'] = $code;
    unset($state_data['created_at']);

    return $state_data;
  }

  public static function get_access_token(string $ouath_code, string $social_name): string {
    
    $social_official_data = \SocialsManager\Providers\Official\OfficialUtils::get_social_official_data($social_name);

    $response = wp_remote_post( static::$access_token_base_url, [
      'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
      'body'    => static::get_token_request_body($ouath_code, $social_official_data)
    ]);

    if (is_wp_error($response)) {
      throw new \RuntimeException($response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if(empty($body['access_token'])) {
      throw new RuntimeException(__('Failed to retrieve access token.', SM_SLUG));
    }

    return $body['access_token'];
  }

  public static function validate_social(array $added_social, array $fetched_data): array {

    $fields_map       = static::get_fields_map();
    $selected_social  = static::select_compatible_social($fetched_data);
    $social_fields    = SocialUtils::filter_social_fields($selected_social, $fields_map);
    $final_array      = array_merge($social_fields, $added_social);
    $rate             = static::get_engagement_rate($fetched_data);
    $avatar_url       = static::get_avatar_url($selected_social);
    $attachment_id    = APIUtils::get_avatar($avatar_url, $final_array['name'], $final_array['social_name']);

    $final_array['avatar'] = $attachment_id;
    $final_array['engagement_rate'] = $rate;

    return $final_array;
  }

  public static function generate_body(string|array|null $username = null): array {
    
    $social_official_datas  = \SocialsManager\Providers\Official\OfficialUtils::get_social_official_data(static::$social_label);
    $client_id              = sanitize_text_field( $social_official_datas['client-id'] );
    $redirect_uri           = sanitize_text_field( $social_official_datas['redirect-uri'] );
    $state                  = wp_generate_password( 32, false, false );

    set_transient( 'sm_oauth_state_' . static::$social_label . '_' . $state, [
      'user_id'     => get_current_user_id(),
      'social_name' => static::$social_label,
      'created_at'  => time()
    ], 10 * MINUTE_IN_SECONDS );
    
    $body = static::get_oauth_body_args($client_id, $redirect_uri, $state);

    return apply_filters( 'sm_generate_official_request_body', $body );
  }

  public function generate_oauh_url(): string {
    $data = self::generate_body();

    $oauth_url = static::$oauth_code_base_url . '?' . http_build_query($data);
    return $oauth_url;
  }
}
