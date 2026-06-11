<?php

namespace SocialsManager\Providers;

use SocialsManager\Providers\SocialInterface as SocialInterface;

if(!defined("ABSPATH")) exit;

interface ProviderInterface {
  public function create_social_instance(array $social_data): SocialInterface;

  public static function get_provider_api_settings_fields_template_path(): string;

  public static function get_label(): string;

  public static function get_provider_socials(): array;

  public function validate_request(array $social_data, int $user_id, string $error_title): void;

  public static function get_front_data_receiver_template(array $social): string;
}