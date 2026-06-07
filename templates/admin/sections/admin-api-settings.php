<?php 

use \SocialsManager\Utils\RouteUtils as RouteUtils;

?>

<div class="field">

  <?php 
  
  echo RouteUtils::get_sm_template( 
    'sm_get_social_provider_selection_field', 
    SM_TMP . 'admin/sections/parts/social-provider-selection-field.php',
    [
      'social' => $social,
    ]
  ); 

  $social_supported_providers = \SocialsManager\Services\SocialsService::get_social_supported_providers($social['social_name']);
  $app_supported_providers    = \SocialsManager\Utils\APIUtils::get_supported_providers();

  foreach($social_supported_providers as $provider) {
    $class = $app_supported_providers[$provider]['class'];
    $path = $class::get_provider_api_settings_fields_template_path();
    if( file_exists($path . 'receive-provider-metadatas.php') ) {
      echo RouteUtils::get_sm_template( 
        'sm_get_' . $provider .'_receive_provider_metadatas_template', 
        $path . 'receive-provider-metadatas.php', 
        [
          'social'              => $social,
        ]
      );
    }
  }

  ?>
</div>