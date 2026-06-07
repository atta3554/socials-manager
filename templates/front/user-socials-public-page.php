<?php 

/**
 * User Socials Public Page
 *
 * @package     Socials Manager
 * @subpackage  Socials-Manager/templates/front
 * @author      Elmira ashrafi
 * @link        ashraf.at6876@gmail.com
 * @version     1.0.0
 * @since       1.0.0
*/

use SocialsManager\Utils\SocialUtils as SocialUtils;

if(!defined("ABSPATH")) exit;

?>
<div class="user-socials">
  
  <?php 
  if(empty($user_socials)) {
    echo "<h2 style='text-align: center;'>No socials found for this user</h2>";
    return;
  } 
  ?>

  <div class="socials-wrapper" data-user-id=<?php echo esc_attr($user_id) ?>>
    <?php foreach($user_socials as $social) {
      $social_name    = $social['social_name'];
      $social_texture = SM_ASSETS_URL . 'textures/' . $social_name . '.png';
      $avatar_id      = $social['avatar'];
      $avatar_url     = get_attachment_link( $avatar_id );
      $members        = SocialsManager\Utils\UserUtils::format_count($social['members_count']);
      $final_social   = SocialUtils::extend_social($social);

      echo SocialsManager\Utils\RouteUtils::get_sm_template( 
        'sm_get_social_dashboard_box', 
        SM_TMP . 'front/sections/public-social/social-box.php',  
        [
          'social_name'     => $social_name,
          'final_social'    => $final_social,
          'members'         => $members,
          'avatar_url'      => $avatar_url,
          'social_texture'  => $social_texture
        ]
      );
    } ?>
    
  </div>
</div>