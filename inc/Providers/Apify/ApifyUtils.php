<?php

namespace SocialsManager\Providers\Apify;

if(!defined("ABSPATH")) exit;

final class ApifyUtils {
  public static function get_all_actor_ids(): array {
    return get_option(SM_APIFY_ACTOR_IDS_OPTION, []);
  }

  public static function get_single_actor_id(string $social): string {
    $actor_ids = self::get_all_actor_ids();
    return $actor_ids[$social] ?? throw new \RuntimeException(__("Actor Not Found! Ask admin to add " . $social . " actor", SM_SLUG));
  }

  public static function save_actor_ids(array $actor_ids): bool {
    return update_option( SM_APIFY_ACTOR_IDS_OPTION , $actor_ids );
  }
}