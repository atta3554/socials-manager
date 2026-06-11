<?php

namespace SocialsManager\Providers;

if(!defined("ABSPATH")) exit;

interface SocialInterface {

  public function fetch(array $social_data, int $user_id): array;

  public static function generate_body(string|array $username = null): array;
  
  public static function get_fields_map(): array;
  
  public static function select_compatible_social(array $fetched_social): array;
  
  public static function get_engagement_rate(array $social_body, ?int $members_count = null): int|float;

}