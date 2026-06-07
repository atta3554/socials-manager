<?php 

namespace SocialsManager\Utils;

if(!defined('ABSPATH')) exit();

class APIUtils {

  public static function get_supported_providers(): array {
    
    $providers = [
      'apify' => [
        'label' => __(\SocialsManager\Providers\Apify\ApifySocialsFactory::get_label(), SM_SLUG),
        'class' => \SocialsManager\Providers\Apify\ApifySocialsFactory::class,
      ],
      'official' => [
        'label' => __(\SocialsManager\Providers\Official\OfficialSocialsFactory::get_label(), SM_SLUG),
        'class' => \SocialsManager\Providers\Official\OfficialSocialsFactory::class,
      ],
      'serpapi' => [
        'label' => __(\SocialsManager\Providers\SerpApi\SerpApiSocialsFactory::get_label(), SM_SLUG),
        'class' => \SocialsManager\Providers\SerpApi\SerpApiSocialsFactory::class,
      ],
    ];

    return apply_filters('sm_supported_providers', $providers);
  }

  public static function get_selected_api_providers(): array {
    return get_option(SM_SELECTED_API_PROVIDER_OPTION, []);
  }

  public static function save_selected_api_providers(array $selected_providers): bool {
    return update_option( SM_SELECTED_API_PROVIDER_OPTION, $selected_providers );
  }

  public static function save_api_keys(#[\SensitiveParameter] array $api_keys): bool {
    return update_option( SM_API_KEYS_OPTION, $api_keys );
  }

  public static function get_api_keys(): array {
    return get_option( SM_API_KEYS_OPTION, [] );
  }

  public static function social_has_provider(string $social_name): bool {
    $selected_providers = self::get_selected_api_providers();
    return isset($selected_providers[$social_name]) && !empty($selected_providers[$social_name]);
  }

  public static function is_selected_provider_for_social(string $social_name, string $provider): bool {
    $selected_providers = self::get_selected_api_providers();

    if( !self::social_has_provider($social_name) ) return false;

    return $selected_providers[$social_name] === $provider;
  }

  public static function get_ext($content_type, $image) {
    $accepted_mimes = \SocialsManager\Utils\UserUtils::get_user_accepted_mimes();
    
    if($content_type){ 
      if( strpos(';', $content_type) !== false ){
        $header_parts = explode(';', $content_type);
        $mime = strtolower(trim($header_parts[0]));
      } else {
          $mime = $content_type;
      }
      
      if(in_array($mime, $accepted_mimes)) {
        $ext = $accepted_mimes[$mime];
      }else {
        $ext = '.jpg';
      }
    } else {
      $mime = getimagesizefromstring($image)['mime'];
      if($mime) {
        if(in_array($mime, $accepted_mimes)) {
          $ext = $accepted_mimes[$mime];
        } else {
          $ext = '.jpg';
        }
      }else {
        $ext = '.jpg';
      }
    }
    return $ext;
  }

  public static function get_avatar(string $url, string $username, string $social): int|\WP_Error {
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $default_avatar_attachment_id = \SocialsManager\Utils\RouteUtils::get_default_avatar_attachment_id();
    $res = wp_remote_get($url);
    
    if(is_wp_error($res)) {
      return $default_avatar_attachment_id;
    }
    
    $code = wp_remote_retrieve_response_code($res);
    
    if($code !== 200) {
      return $default_avatar_attachment_id;
    }
    
    $content_type = wp_remote_retrieve_header($res, 'content-type');
    $image        = wp_remote_retrieve_body($res);
    
    $ext          = self::get_ext($content_type, $image);
    $file_name    = sanitize_file_name($username . '-' . $social . "-avatar") . '.' . $ext;
    $upload       = wp_upload_bits($file_name, null, $image);
    
    if($upload['error'] !== false) {
      return $default_avatar_attachment_id;
    }
    
    $file_array = array(
      'name'=> $file_name,
      'tmp_name'=> $upload['file']
    );

    $desc = sanitize_text_field($social . " avatar of " . $username);
    
    $attachment_id = media_handle_sideload($file_array, 0, $desc);
    
    if(is_wp_error($attachment_id)) {
      return $default_avatar_attachment_id;
    }
    
    return $attachment_id;
  }
}