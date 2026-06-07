<?php

namespace SocialsManager\Providers\SerpApi;

if(!defined("ABSPATH")) exit;

class SerpApiSocialsFactory extends \SocialsManager\Providers\AbstractProvider {

  protected static $label = 'Serp Api';

  public static function get_provider_socials(): array {
    
    $serapi_supported_socials = array(
      'instagram' => \SocialsManager\Providers\SerpApi\Socials\Instagram::class,
      'youtube'   => \SocialsManager\Providers\SerpApi\Socials\Youtube::class,
      'google'  => \SocialsManager\Providers\SerpApi\Socials\Google::class,
    );
  
    $serapi_filtered_socials = apply_filters( 'sm_serapi_socials', $serapi_supported_socials );

    return \SocialsManager\Utils\SocialUtils::filter_allowed_socials($serapi_filtered_socials);
  }

  public static function get_provider_api_settings_fields_template_path(): string {
    return SM_TMP . 'admin/providers/serpapi/';
  }
}