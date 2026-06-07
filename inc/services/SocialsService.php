<?php

namespace SocialsManager\Services;

use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\APIUtils as APIUtils;
use SocialsManager\Services\ProviderFactory as ProviderFactory;
use SocialsManager\Providers\SocialInterface as SocialInterface;


if(!defined("ABSPATH")) exit;

class SocialsService {
  public static function get_app_socials_providers(): array {
    $app_supported_socials    = SocialUtils::get_supported_socials();
    $social_providers_map     = [];

    foreach($app_supported_socials as $social_datas) {
      
      $social = isset($social_datas['social_name']) 
      && !empty($social_datas['social_name']) 
      ? $social_datas['social_name'] 
      : throw new \InvalidArgumentException(__("Invalid social format", SM_SLUG));

      $social_providers_map[$social] = self::get_social_supported_providers($social);
    }

    return $social_providers_map;
  }

  public static function get_social_supported_providers(string $social): array {
    $providers                = [];
    $app_supported_providers  = APIUtils::get_supported_providers();

    foreach($app_supported_providers as $provider_name=>$provider_values) {

      $provider_supported_socials = isset($provider_values['class'])
      && !empty($provider_values['class']) 
      && class_exists($provider_values['class'])
      ? $provider_values['class']::get_provider_socials()
      : throw new \InvalidArgumentException(__(sprintf("Invalid %s class", $provider_name), SM_SLUG));

      if(isset($provider_supported_socials[$social]) && !empty($provider_supported_socials[$social])) {
        $providers[] = $provider_name;
      }
    }

    return $providers;
  }
}