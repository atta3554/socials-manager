<?php 

/**
 * Admin API Settings
 *
 * @package     Socials Manager
 * @subpackage  Socials-Manager/tmp/admin
 * @author      Elmira ashrafi
 * @link        ashraf.at6876@gmail.com
 * @version     1.0.0
 * @since       1.0.0
*/

use SocialsManager\Utils\APIUtils as APIUtils;
use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\RouteUtils as RouteUtils;

use SocialsManager\Services\SocialsService as SocialsService;

if(!defined('ABSPATH')) exit();

$allowed_socials_count = SocialUtils::get_user_allowed_socials_count();

?>

<div class="wrap">
  <div>
      <h1><?php __("API settings", SM_SLUG) ?></h1>
  </div>

  <?php if(isset($_GET['success']) AND $_GET['success'] === 'true') : ?>
    <div class="notice notice-success">
      <p><?php _e("Changes saved successfully", SM_SLUG) ?></p>
    </div>
  <?php endif; ?>

  <?php if(isset($_GET['error']) AND $_GET['error'] === 'true') : ?>
    <div class="notice notice-error">
      <p><?php _e("Failed to save changes", SM_SLUG) ?></p>
    </div>
  <?php endif; ?>

  <div class="providers">
    <form method="POST" action="<?php echo admin_url('admin-post.php') ?>">
      
      <?php wp_nonce_field( "sm_save_provider", SM_NONCE_NAME ) ?>
      <input type="hidden" name="action" value="save_provider">
      
      <div class="field-box">
        <?php foreach(SocialUtils::get_allowed_Socials() as $social) {
          echo RouteUtils::get_sm_template(
            'sm_get_' . $social['social_name'] .'_admin_api_settings_section', 
            SM_TMP . 'admin/sections/admin-api-settings.php', 
            [
              'social' => $social,
            ]
          );
        }?>
      </div>

      <div class="field-box">
        <p class="big-text my-0">Enter your API Keys</p>
        <?php foreach(APIUtils::get_supported_providers() as $provider=>$value) {
          echo RouteUtils::get_sm_template( 
            'sm_get_' . $provider . '_receive_api_keys_template', 
            SM_TMP . 'admin/providers/' . $provider . '/receive-api-key.php',
            [
              'api_keys' => APIUtils::get_api_keys()
            ]
          );
        }?>
      </div>

      <div class="field-box">
        <p class="big-text my-0"><?php echo __('How many social media accounts can users have on a single social media platform?', SM_SLUG) ?></p>
        <div class="field mt-2">
          <label class="me-5" for="single_social"><?php echo __('Only one account for each platform') ?></label>
          <input type="radio" name="allowed_socials_count" id="single_social" value="single_social" <?php checked( $allowed_socials_count, 'single_social' ) ?>>
        </div>
        <div class="field">
          <label class="me-5" for="multiple_social"><?php echo __('multiple accounts for each platform') ?></label>
          <input type="radio" name="allowed_socials_count" id="multiple_social" value="multiple_social" <?php checked( $allowed_socials_count, 'multiple_social' ) ?> >
        </div>
      </div>

      <div class="field-box">
        <p class="big-text">Select desired socialss</p>
        <?php foreach(SocialUtils::get_supported_socials() as $social) {
          echo RouteUtils::get_sm_template(
            'sm_get_' . $social['social_name'] . '_desired_selection_field',
            SM_TMP . 'admin/sections/parts/desired-social-selection-box.php',
            [
              'social'=> $social
            ]
          );
        } ?>
        </select>
      </div>

      <?php do_action( 'sm_admin_api_additional_fields' ) ?>

      <input type="submit" value="<?php esc_attr_e("Submit", SM_SLUG) ?>">
      
    </form>
  </div>
  
</div>