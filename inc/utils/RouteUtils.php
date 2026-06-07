<?php 

namespace SocialsManager\Utils;

if(!defined('ABSPATH')) exit();

class RouteUtils {
  public static function is_admin_api_page() {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    return $page === SM_SLUG;
  }

  public static function is_admin_checkup_page() {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    return $page === SM_SLUG . '-checkup';
  }

  public static function is_admin_shortcodes_page() {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    return $page === SM_SLUG . '-shortcodes';
  }

  public static function redirect_back($args) {
    
    $referer = wp_get_referer() ?: admin_url();
    
    $url = add_query_arg( $args, $referer );

    wp_safe_redirect($url);
    exit();
  }

  public static function create_page(string $slug, string $title, string $content, string $option_name): void {
    $existing = get_page_by_path($slug);

    if ($existing) {
      update_option($option_name, $existing->ID);
      return;
    }

    $page_id = wp_insert_post([
      'post_title'   => $title,
      'post_name'    => $slug,
      'post_content' => '[' . $content . ']',
      'post_status'  => 'publish',
      'post_type'    => 'page',
    ]);

    if (is_wp_error($page_id)) {
      throw new \RuntimeException(__("failed to create plugin pages! try to create pages manually and add shortcodes to them"));
    }

    update_option($option_name, $page_id);
  }

  public static function get_sm_template(string $filter, string $path, array $args =[]): string {

    $template = apply_filters($filter , $path);

    if(!file_exists($template)) {
      return '';
    }

    if(!empty($args)) {
      extract($args, EXTR_SKIP);
    }
    
    ob_start();
    require $template;
    return ob_get_clean();
  }

  public static function get_asset_file_contents(string $relative_path): string {
    $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');

    if(strpos($relative_path, '../') !== false) {
      return '';
    }

    $asset_root = trailingslashit(wp_normalize_path(SM_ASSETS_PATH));
    $path       = wp_normalize_path(SM_ASSETS_PATH . $relative_path);

    if(strpos($path, $asset_root) !== 0 || !is_readable($path)) {
      return '';
    }

    $contents = file_get_contents($path);
    return is_string($contents) ? $contents : '';
  }

  public static function create_attachment_from_local_files(string $path): int {
    if(!file_exists($path) || !is_readable($path)) {
      throw new \RuntimeException(__("Default avatar file not found", SM_SLUG));
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload = wp_upload_bits( basename($path), null, file_get_contents($path) );

    if(!empty($upload['error'])) {
      throw new \RuntimeException(__($upload['error'], SM_SLUG));
    }

    $file_path  = $upload['file'];
    $file_url   = $upload['url'];
    $file_type  = wp_check_filetype( basename($file_path), null );

    $attachment_id = wp_insert_attachment([
      'post_mime_type'  => $file_type['type'],
      'post_title'      =>sanitize_file_name( pathinfo($file_path, PATHINFO_FILENAME) ),
      'post_content'    => '',
      'post_status'     => 'inherit',
      'guid'            => $file_url
    ], $file_path);

    if(is_wp_error( $attachment_id )) {
      throw new \RuntimeException(__($attachment_id->get_error_message(), SM_SLUG));
    }

    $metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
    wp_update_attachment_metadata( $attachment_id, $metadata );

    return (int) $attachment_id;
  }

  public static function get_default_avatar_attachment_id(): int {
    return get_option(SM_DEFAULT_AVATAR_ATTACHMENT_ID_OPTION, 0);
  }

  public static function get_dashboard_page_id(): int {
    return get_option( SM_USER_SOCIALS_DASHBOARD_PAGE_ID_OPTION, true );
  }

  public static function get_profile_page_id(): int {
    return get_option( SM_USER_SOCIALS_PUBLIC_PAGE_ID_OPTION, true );
  }
}
