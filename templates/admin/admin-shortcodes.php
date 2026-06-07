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

if(!defined('ABSPATH')) exit(); ?>

<div class="wrap">
  <div>
      <h1><?php __("API settings", SM_SLUG) ?></h1>
  </div>

  <div class="shortcodes">
    <?php foreach($shortcode_maps as $label=>$shortcode) : ?>
      <div class="shortcode">
        <div class="shortcode-info">
          <label for="<?php echo esc_attr($shortcode['value']) ?>"><?php esc_html_e(__($label, SM_SLUG)) ?></label>
          <input type="text" id="<?php echo esc_attr($shortcode['value']) ?>" value="<?php echo esc_attr($shortcode['value']) ?>" disabled>
        </div>
        <span class="details-icon" >!</span>
        <div class="shortcode-details">
          <p><?php echo esc_html($shortcode['description']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>