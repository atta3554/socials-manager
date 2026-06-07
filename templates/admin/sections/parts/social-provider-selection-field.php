<?php 
use \SocialsManager\Utils\APIUtils as APIUtils;

$supported_providers    = APIUtils::get_supported_providers(); 
$selected_providers     = APIUtils::get_selected_api_providers();
$label                  = "Select " . $social['social_label'] . " provider";
$social_name            = $social['social_name'];
?>
<div class="<?php echo esc_attr($social_name . '-provider') ?>">
  <label for="<?php echo esc_attr($social_name . '-provider') ?>" class="big-text me-5"><?php esc_html_e($label, SM_SLUG) ?></label>
  <select id="<?php echo esc_attr($social_name . '-provider') ?>" name="<?php echo esc_attr('providers[' . $social_name . ']') ?>">
    <option value=""><?php esc_html_e($label, SM_SLUG) ?></option>
    <?php foreach(\SocialsManager\Services\SocialsService::get_social_supported_providers($social_name) as $provider) : 
      $provider_label = $supported_providers[$provider]['label'];
    ?>
      <option value="<?php echo esc_attr($provider) ?>" <?php isset($selected_providers[$social_name]) ? selected( $selected_providers[$social_name], $provider ) : null ?>><?php esc_html_e($provider_label, SM_SLUG) ?></option>
    <?php endforeach; ?>
  </select>
</div>