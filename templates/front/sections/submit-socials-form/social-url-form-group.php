<?php 
$social_name = sanitize_html_class($social['social_name']);
$social_url  = $social[sanitize_key( $social_name . '-url' )] ?? '';
?>
<fieldset class="<?php echo esc_attr($social_name) ?>" data-social-url="<?php echo esc_attr($social_url) ?>">
  <div class="form-group">
    <label class="form-group-title"><?php esc_html_e( $social['label_for_url'] .' :', SM_SLUG);?></label> 
    <input type="text" value="<?php echo esc_attr($social_url) ?>" name="<?php echo sanitize_text_field('social[' . $social['social_name'] . '-url]') ?>" class="form-control" placeholder="<?php esc_attr_e($social['placeholder_for_url'], SM_SLUG);?>" autocomplete="off">
  </div>
</fieldset>