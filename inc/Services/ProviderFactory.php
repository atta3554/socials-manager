<?php

namespace SocialsManager\Services;

use SocialsManager\Providers\ProviderInterface as ProviderInterface;
use SocialsManager\Providers\Official\OfficialSocialsFactory as OfficialSocialFactory;
use SocialsManager\Providers\Apify\ApifySocialsFactory as ApifySocialsFactory;
use SocialsManager\Providers\SerpApi\SerpApiSocialsFactory as SerpApiSocialsFactory;

use SocialsManager\Utils\APIUtils;

if(!defined("ABSPATH")) exit;

class ProviderFactory {
  public static function create_provider_instance( string $social_name): ProviderInterface {

    $selected_provider  = APIUtils::get_selected_api_providers();

    if(empty($social_name) || empty($selected_provider[$social_name])) {
      throw new \InvalidArgumentException(__('No provider selected for this social.', SM_SLUG));
    }

    $social_provider    = $selected_provider[$social_name];

    $providers = APIUtils::get_supported_providers();

    if(empty($providers[$social_provider]['class']) || ! class_exists($providers[$social_provider]['class'])) {
      throw new \InvalidArgumentException(__("Invalid Social provider", SM_SLUG));
    }

    $instance = new $providers[$social_provider]['class']();

    if(! $instance instanceof ProviderInterface) {
      throw new \RuntimeException(__('Invalid provider class.', SM_SLUG));
    }
    
    return $instance;
  }
}
