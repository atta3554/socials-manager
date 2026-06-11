<?php 

namespace SocialsManager\Services;

if(!defined("ABSPATH")) exit;

class HttpService {

  public function register($loader) {
    $loader->add_action('http_api_curl', $this, 'increase_curl_ssl_timeout', 10, 3 );
    $loader->add_action('rest_api_init', $this, 'register_routes' );
  }

  public static function register_routes() {
    
    $official_name        = 'official';
    $supported_providers  = \SocialsManager\Utils\APIUtils::get_supported_providers();
    $official_class       = $supported_providers[$official_name]['class'] ?? '';

    if(empty($official_class) || !class_exists($official_class)) {
      return;
    }

    $official_socials     = $official_class::get_provider_socials();
    
    foreach(\SocialsManager\Utils\APIUtils::get_selected_api_providers() as $social=>$provider) {
      if($provider === $official_name) {
        if(empty($official_socials[$social]) || !is_callable([$official_socials[$social], 'handle_oauth_callback'])) {
          continue;
        }

        register_rest_route(SM_REST_ROUTE_NAMESPACE, SM_REST_ROUTE_PATH . $social, [
          'methods'             => "GET",
          'callback'            => [$official_socials[$social], 'handle_oauth_callback'],
          'permission_callback' => '__return_true',
        ]);
      }
    }
  }

  public static function increase_curl_ssl_timeout($handle, array $args, string $url): void {
   
    // if(strpos( $url, 'apify' ) === false && strpos( $url, 'serpapi' ) === false) return;

    $posted_social = $_POST['social'] ?? [];
    $social_name   = is_array($posted_social) && isset($posted_social['social_name']) ? sanitize_key(wp_unslash($posted_social['social_name'])) : '';
    $timeout      = 30;
      
    if(strpos( $url, 'apify' ) !== false && $social_name === 'tiktok') {
      $timeout    = 50;
    }
      
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
  }

  public static function fetch(string $url, ?array $args = []): array {

    $args = wp_parse_args($args ?? [], [
      'timeout' => 20,
    ]);

    $res = wp_remote_request( $url, $args );

    if (is_wp_error($res)) {

      $error_message  = $res->get_error_message();
      $error_code     = $res->get_error_code();

      if( strpos($error_message, "cURL error 28") !== false) {
        return [
          'success' => false,
          'message' => __("Request timed out! please check your network connectivity", SM_SLUG), 
          'code'    => 408,
          'error_code' => $error_code,
          'body'    => ''
        ];
      }

      return [
        'success' => false,
        'message' => sanitize_text_field($error_message),
        'code'    => 500,
        'error_code' => $error_code,
        'body'    => '',
      ];
    }

    $code             = (int) wp_remote_retrieve_response_code($res);
    $response_message = wp_remote_retrieve_response_message($res);
    $raw_body         = wp_remote_retrieve_body($res);
    $body             = json_decode($raw_body, true);

    if(json_last_error() !== JSON_ERROR_NONE) {
      return [
        'success' => false,
        'message' => $response_message ? sanitize_text_field($response_message) : __('Invalid JSON response.', SM_SLUG),
        'code'    => $code ?: 500,
        'body'    => $raw_body,
      ];
    }

    if ($code < 200 || $code >= 300 || empty($body) || !is_array($body)) {
      return [
        'success' => false,
        'message' => self::get_response_error_message($body, $response_message),
        'code'    => $code ?: 500,
        'body'    => $body,
      ];
    }

    return [
        'success' => true,
        'message' => '',
        'code'    => $code,
        'body'    => $body,
    ];
  }

  private static function get_response_error_message($body, string $response_message = ''): string {

    if(is_array($body)) {
      if(!empty($body['error']['message'])) {
        return sanitize_text_field($body['error']['message']);
      }

      if(!empty($body['message'])) {
        return sanitize_text_field($body['message']);
      }

      if(!empty($body['errors'][0]['message'])) {
        return sanitize_text_field($body['errors'][0]['message']);
      }
    }

    return $response_message ? sanitize_text_field($response_message) : __('Request failed.', SM_SLUG);
  }
}
