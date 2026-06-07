<?php

/**
 * Admin Checkup Runner
 *
 * @package     Socials Manager
 * @subpackage  Socials-Manager/tmp/admin
 * @author      Elmira ashrafi
 * @link        ashraf.at6876@gmail.com
 * @version     1.0.0
 * @since       1.0.0
*/

if (!defined('ABSPATH')) {
    exit();
}

?>

<div class="wrap"> 
  <div>
    <h1><?php esc_html_e("Validate socials", SM_SLUG) ?></h1>
  </div>
    
  <div class="target_specification">
    <div>
      <h3><?php esc_html_e("Which user(s) you would mind to validate", SM_SLUG) ?></h3>
    </div>
    <div>
      <input type="hidden" id="invalid_validation_type" value="<?php esc_attr_e('please specify a user first, or select "validate all users" option if you want to validate all users', SM_SLUG) ?>">
      <input type="hidden" id="invalid_values" value="<?php esc_attr_e('Invalid values submited', SM_SLUG) ?>">
      <div class='form-control'>
        <input id="all_users" type='radio' name='validate_target' value='all_users' checked>
        <label for='all_users'><?php esc_html_e("Validate All Users", SM_SLUG) ?></label>
      </div>
      <div class='form-control'>
        <input id="specefic_user" type='radio' name='validate_target' value='specefic_user'>
        <label for='specefic_user'><?php esc_html_e("Specefic User", SM_SLUG) ?></label>
      </div> 
    </div>
  </div>
  
  <input type='hidden' id='user-id' name='user-id' >
  <input type='hidden' id='ajax_url' name='ajax_url' value='<?php echo admin_url('admin-ajax.php') ?>'>

  <fieldset>
    <input type='hidden' id='action' name='action' value='social_validation' >
    <?php wp_nonce_field('checkup_nonce', SM_NONCE_NAME) ?>
  </fieldset>
      
  <div class="users">
      <ul>
      <?php foreach ($users as $user) : ?>
          <li class='user' data-id='<?php echo esc_attr($user->ID) ?>'> <?php echo esc_html($user->user_nicename) ?></li>
      <?php endforeach; ?>
      </ul>
  </div>
  
  <div class="validation_btns">
    <div class="single">
      <?php foreach ($socials as $social) : ?>
        <div class='validation_box'>
          <p><?php esc_html_e("validate " . $social['social_label'], SM_SLUG) ?></p>
          <button id="<?php echo esc_attr($social['social_name']) ?>"><?php esc_html_e("Validate", SM_SLUG) ?></button>
        </div>
      <?php endforeach; ?>
    </div>
        
    <div class="all">
        <p><?php esc_html_e("validate All socials Once", SM_SLUG) ?></p> 
        <button id="all"><?php esc_html_e("Validate All", SM_SLUG) ?></button>
    </div>
  </div>
</div>