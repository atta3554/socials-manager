<div class="form-group social">
  <label for="social_media" class="form-group-title"><?php esc_html_e('select your social media :', SM_SLUG);?></label>
  <select id="social_media" name="social[social_name]" class="form-control" autocomplete="off" data-placeholder="<?php esc_attr_e('select your social media', SM_SLUG);?>">
      <option value=""><?php esc_html_e('select your social media', SM_SLUG);?></option>
      <?php foreach($allowed_socials as $social) : ?>
          <option value="<?php echo esc_attr($social['social_name'], SM_SLUG) ?>"><?php esc_html_e($social['social_label'], SM_SLUG);?></option> 
      <?php endforeach; ?> 
  </select>
</div>