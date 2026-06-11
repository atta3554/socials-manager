<?php

namespace SocialsManager\Providers\Official;

if(!defined("ABSPATH")) exit;

final class OfficialUtils {
  public static function get_all_officials_datas(): array {
    return get_option(SM_OFFICIAL_DATAS_OPTION, []);
  }

  public static function get_social_official_data(string $social_name): array {
    $official_datas = self::get_all_officials_datas();
    return $official_datas[$social_name] ?? [];
  }

  public static function save_officials_datas(array $official_datas): bool {
    return update_option( SM_OFFICIAL_DATAS_OPTION , $official_datas );
  }

  public static function get_redirect_url($social_name) {
    return rest_url( SM_REST_ROUTE_NAMESPACE . SM_REST_ROUTE_PATH . $social_name );
  }
}
