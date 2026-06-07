<?php

namespace SocialsManager\Providers\Apify;

if(!defined("ABSPATH")) exit;

class ApifySocialsFactory extends \SocialsManager\Providers\AbstractProvider {

  protected static $label = 'Apify.org';

  public static function get_provider_socials(): array {
    
    $apify_supported_socials = array(
      'instagram' => \SocialsManager\Providers\Apify\Socials\Instagram::class,      
      'youtube'   => \SocialsManager\Providers\Apify\Socials\Youtube::class,      
      'tiktok'    => \SocialsManager\Providers\Apify\Socials\Tiktok::class,      
      'snapchat'  => \SocialsManager\Providers\Apify\Socials\Snapchat::class,      
    );
  
    $apify_filtered_socials = apply_filters( 'sm_apify_socials', $apify_supported_socials );

    return \SocialsManager\Utils\SocialUtils::filter_allowed_socials($apify_filtered_socials);
  }

  public static function get_provider_api_settings_fields_template_path(): string {
    return SM_TMP . 'admin/providers/apify/';
  }
}