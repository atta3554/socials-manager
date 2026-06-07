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
    $official_class       = $supported_providers[$official_name]['class'];
    $official_socials     = $official_class::get_provider_socials();
    
    foreach(\SocialsManager\Utils\APIUtils::get_selected_api_providers() as $social=>$provider) {
      if($provider === $official_name) {
        register_rest_route(SM_REST_ROUTE_NAMESPACE, SM_REST_ROUTE_PATH . $social, [
          'methods'             => "GET",
          'callback'            => [$official_socials[$social], 'handle_oauth_callback'],
          'permission_callback' => '__return_true',
        ]);
      }
    }
  }

  public static function increase_curl_ssl_timeout($handle, array $args, string $url): void {
   
    // if(!str_contains( $url, 'apify' ) && !str_contains( $url, 'serpapi' )) return;

    $social_name  = $_POST['social']['social_name'] ?? '';
    $timeout      = 30;
      
    if(str_contains( $url, 'apify' ) && $social_name === 'tiktok') {
      $timeout    = 50;
    }
      
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($handle, CURLOPT_TIMEOUT, $timeout);
  }

  public static function fetch(string $url, ?array $args = []): array {

    $res = wp_remote_request( $url, $args );

    if (is_wp_error($res)) {

      $error_message  = $res->get_error_message();
      $error_code     = $res->get_error_code();

      if( strpos($error_message, "cURL error 28") !== false) {
        return [
          'success' => false,
          'message' => __("Request timed out! please check your network connectivity", SM_SLUG), 
          'code'    => $error_code,
          'body'    => ''
        ];
      }

      return [
        'success' => false,
        'message' => __($error_message, SM_SLUG),
        'code'    => $error_code,
        'body'    => '',
      ];
    }

    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);

    $body = json_decode($body, true);

    if ($code < 200 || $code >= 300 || empty($body) || !is_array($body)) {error_log(print_r($res, true));
      return [
        'success' => false,
        'message' => __('Request failed.', SM_SLUG),
        'code'    => $code,
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
}