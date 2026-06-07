<?php 
$actor_ids = \SocialsManager\Providers\Apify\ApifyUtils::get_all_actor_ids();
$social_name = $social['social_name'];
$social_label= $social['social_label'];
?>
<div class="<?php echo esc_attr($social_name . "-actor-id") ?> <?php echo \SocialsManager\Utils\APIUtils::is_selected_provider_for_social($social_name, 'apify') ? 'active' : '' ?>">
  <div class="social-actor-settings">
    <label for="<?php echo $social_name ?>-actor-id">Enter <?php echo $social_label ?> Actor ID</label>
    <input type="text" id="<?php echo $social_name ?>-actor-id" name="actor_ids[<?php echo $social_name ?>]" value="<?php echo isset($actor_ids[$social_name]) ? esc_attr($actor_ids[$social_name]) : '' ?>">
  </div>
</div>