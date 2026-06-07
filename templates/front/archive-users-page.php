<?php 

/**
 * Archive Users
 *
 * @package     Socials Manager
 * @subpackage  Socials-Manager/templates/front
 * @author      Elmira ashrafi
 * @link        mailto:ashraf.at6876@gmail.com
 * @version     1.0.0
 * @since       1.0.0
*/

if(!defined("ABSPATH")) exit;

?>

<div class="users">
  <?php foreach($users as $user) : ?>
    <div class="user">
      <h3><a href="<?php echo esc_url( home_url( '/socials-public/' . rawurlencode($user->user_login) ) ) ?>"><?php echo esc_html($user->user_nicename) ?></a></h3>
    </div>
  <?php endforeach; ?>
</div>