<?php 

/**
 * User Socials Dashboard
 *
 * @package     Socials Manager
 * @subpackage  Socials-Manager/templates/front
 * @author      Elmira ashrafi
 * @link        ashraf.at6876@gmail.com
 * @version     1.0.0
 * @since       1.0.0
*/

use SocialsManager\Utils\SocialUtils as SocialUtils;
use SocialsManager\Utils\RouteUtils as RouteUtils;

if(!defined("ABSPATH")) exit;

?>
<div class="user-dashboard">

  <div class="user-socials" data-user-id="<?php echo esc_attr($user_id) ?>">
    <?php foreach($user_socials as $social) {
      $social_name  = sanitize_key($social['social_name']);
      $social_svg   = RouteUtils::get_asset_file_contents('images/' . $social_name . '.svg');
      $members      = SocialsManager\Utils\UserUtils::format_count($social['members_count']);
      $final_social = SocialUtils::extend_social($social);
      $social_url   = SocialUtils::get_user_social_url($final_social);

      echo RouteUtils::get_sm_template( 
        'sm_get_social_dashboard_box', 
        SM_TMP . 'front/sections/social-dashboard/social-box.php',  
        [
          'social_url'=> $social_url,
          'social_name'=> $social_name,
          'final_social'=> $final_social,
          'members'=> $members,
          'social_svg'=> $social_svg,
        ]
      );
    } ?>
    
  </div>
  <div class="submit-socials-wrapper">
    <?php 
    echo SocialsManager\Utils\RouteUtils::get_sm_template(
      'sm_get_submit_socials_form', 
      SM_TMP . 'front/submit-socials-form.php', 
      [
        'allowed_socials' => $user_socials,
        'form_mode'       => $form_mode,
        'user_id'         => $user_id
      ]
    );
    ?>
  </div>

  <script>
    window.SM_USER_SOCIALS = <?php echo wp_json_encode($user_socials) ?>
  </script>

</div>
