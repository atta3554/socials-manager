<?php

namespace SocialsManager\Utils;

if(!defined("ABSPATH")) exit;

class UserUtils {
  public static function current_user_can_edit($user_id) {
    $user_id = absint($user_id);
    return is_user_logged_in() && ($user_id === get_current_user_id() || current_user_can('edit_user', $user_id));
  }

  public static function sanitize_array(array $array): array {
    return map_deep(wp_unslash($array), 'sanitize_text_field');
  }

  public static function get_user_accepted_mimes() {
    $accepted_mimes = array(
      'image/jpeg'=> 'jpg',
      'image/jpg'=> 'jpg',
      'image/png'=> 'png',
      'image/webp'=> 'webp',
      'image/gif'=> 'gif',
      'image/avif'=> 'avif',
    );

    return apply_filters( 'sm_accepted_mimes', $accepted_mimes );
  }

  public static function sanitize_user_social($socials) {
    $raw_socials      = wp_unslash( $socials );
    $social_name      = isset($raw_socials['social_name']) ? sanitize_key( $raw_socials['social_name'] ) : '';
    $social_url       = $raw_socials[$social_name . "-url"]         ?? '';
    $social_desc      = $raw_socials['description']                 ?? '';
    
    $user_social = [
      'social_name'         => sanitize_text_field( $social_name ),
      'description'         => sanitize_text_field( $social_desc ),
      $social_name . '-url' => esc_url_raw( $social_url ),
    ];

    return apply_filters( 'sm_sanitize_user_social', $user_social );
  }

  public static function format_count(int $count): string {

    if (!$count) {
        return 'value not provided';
    }

    if ($count < 1000) {
        return (string) $count;
    }
    if ($count < 1000000) {
        return number_format($count / 1000, $count < 10000 ? 1 : 0) . 'K';
    }
    if ($count < 1000000000) {
        return number_format($count / 1000000, $count < 10000000 ? 1 : 0) . 'M';
    }

    return number_format($count / 1000000000, 1) . 'B';
  }
}
