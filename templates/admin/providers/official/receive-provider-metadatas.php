<?php 
$social_name = $social['social_name'];
$social_official_data = \SocialsManager\Providers\Official\OfficialUtils::get_social_official_data($social_name);
?>
<div class="<?php echo esc_attr($social_name . "-official-data") ?> <?php echo \SocialsManager\Utils\APIUtils::is_selected_provider_for_social($social_name, 'official') ? 'active' : '' ?>">
  <div class="social-official-settings">
    <label for="<?php echo $social_name ?>-client-id">Enter <?php echo $social['social_label'] ?> client ID</label>
    <input type="text" id="<?php echo $social_name ?>-client-id" name="official_datas[<?php echo esc_attr($social_name) ?>][client-id]" value="<?php echo esc_attr($social_official_data['client-id'] ?? '') ?>">
  </div>
  <div class="social-official-settings">
    <label for="<?php echo $social_name ?>-client-secret">Enter <?php echo $social['social_label'] ?> client secret</label>
    <input type="password" id="<?php echo $social_name ?>-client-secret" name="official_datas[<?php echo esc_attr($social_name) ?>][client-secret]" value="<?php echo esc_attr($social_official_data['client-secret'] ?? '') ?>">
  </div>
  <div class="social-official-settings">
    <label for="<?php echo $social_name ?>-redirect-uri">Enter <?php echo $social['social_label'] ?> redirect uri</label>
    <input type="url" readonly id="<?php echo $social_name ?>-redirect-uri" name="official_datas[<?php echo esc_attr($social_name) ?>][redirect-uri]" value="<?php echo esc_attr(\SocialsManager\Providers\Official\OfficialUtils::get_redirect_url($social_name)) ?>" onclick="this.select()">
  </div>
</div>