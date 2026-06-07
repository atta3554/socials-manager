<div class="social <?php echo esc_attr($social_name) ?>">
  <div class="social-icon">
    <canvas id="<?php echo esc_attr($social_name) ?>" data-icon="<?php echo esc_url($social_texture) ?>"></canvas>
  </div>
  <div class="social-info">
    <div class="username"><strong><?php echo esc_html($final_social['name']) ?></strong></div>
    <div class="biography"><?php echo esc_html(wp_trim_words( $final_social['biography'], 10 )) ?></div>
    <div class="members-info">
      <div class="members">
        <span><?php echo esc_html( $final_social['members_name'] ) ?>: </span>
        <span><strong><?php echo esc_html($members) ?></strong></span>
      </div>
      <div class="engagement-rate">
        <span><?php echo esc_html($final_social['engagement_rate_label']) ?>: </span>
        <span><strong><?php echo esc_html($final_social['engagement_rate']) ?>%</strong></span>
      </div>
    </div>
  </div>
  <div class="avatar">
    <img src="<?php echo esc_url($avatar_url) ?>" alt="social avatar">
  </div>
</div>