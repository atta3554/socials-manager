<div class="checkbox">
  <label for="<?php echo esc_attr($social['social_name'], SM_SLUG) ?>"><?php esc_html_e($social['social_label'], SM_SLUG) ?></label>
  <input type="checkbox" id="<?php echo esc_attr($social['social_name'], SM_SLUG) ?>" name="socials[<?php echo esc_attr($social['social_name'], SM_SLUG) ?>]" <?php checked( in_array($social['social_name'], \SocialsManager\Utils\SocialUtils::get_selected_socials()) ) ?> >
</div>