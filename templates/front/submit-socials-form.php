<?php 

/**
 * Social Submission Form
 * Depends on $form_mode to determine the form location.
 *
 * @package     Socials Manager
 * @subpackage  Socials-Manager/templates/front
 * @author      Elmira ashrafi
 * @link        mailto:ashraf.at6876@gmail.com
 * @version     1.0.0
 * @since       1.0.0
*/

use \SocialsManager\Utils\RouteUtils as RouteUtils;

if(!defined("ABSPATH")) exit;

if(empty($form_mode)) return null;
?>

<div class="submit-socials">
  <form id="submit-socials" >

    <?php if($form_mode === 'edition') {
      echo RouteUtils::get_sm_template( 
        'sm_get_close_edit_form_popup',
        SM_TMP . 'front/sections/submit-socials-form/close-popup-btn.php'
        );
    } ?>
	
    <?php $form_mode === 'submission' ? wp_nonce_field( 'sm_submit_social', SM_NONCE_NAME ) : wp_nonce_field( 'sm_edit_social', SM_NONCE_NAME ) ?>
    <input type="hidden" name="action" value="<?php echo $form_mode === 'edition' ? 'sm_edit_social' : 'sm_submit_social' ?>">
    <input type="hidden" name="user_id" value="<?php echo $user_id ?>">


    <?php if($form_mode === 'submission') {
      echo RouteUtils::get_sm_template(
        'sm_get_social_selection_form_group', 
        SM_TMP . 'front/sections/submit-socials-form/social-selection-form-group.php',
        ['allowed_socials'=> $allowed_socials]
      );
    } ?>
    
    <?php foreach($allowed_socials as $social) {
      $final_social     = \SocialsManager\Utils\SocialUtils::extend_social($social);
      $provider = \SocialsManager\Services\ProviderFactory::create_provider_instance($final_social['social_name']);
      echo $provider->get_front_data_receiver_template($final_social);
    } ?>
      
    <?php echo RouteUtils::get_sm_template( 'sm_get_social_description_form_Group', SM_TMP . 'front/sections/submit-socials-form/social-description-form-group.php', ['form_mode'=> $form_mode] ) ?>

    <?php echo RouteUtils::get_sm_template( 'sm_get_submit_social_btn', SM_TMP . 'front/sections/submit-socials-form/submit-social-btn.php', ['form_mode'=> $form_mode] ) ?>
  </form>
</div>