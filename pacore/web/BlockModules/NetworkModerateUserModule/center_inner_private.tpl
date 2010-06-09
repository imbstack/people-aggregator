<?php
 // global var $_base_url has been removed - please, use PA::$url static variable

  $field_name = 'user_type';
  $value = DISABLED_MEMBER;
  if( $super_user_and_mothership ) {
    $field_name = 'is_active';
    $value = DISABLED;
  }
?>

<div class="description"><?= __("In this page you can moderate users registered on your network") ?></div>

<form name="moderate_users" method="post" action="">
  <fieldset class="center_box">
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>




     <?php if ($links) { ?>
     <table cellpadding="3" cellspacing="3">
      <tr>
        <td><?= __("Select") ?></td>
        <td><?= __("Picture") ?></td>
        <td><?= __("Registered") ?></td>
        <td><?= __("User Name") ?></td>
        <td><?= __("Name") ?></td>
        <td><?= __("Email") ?></td>
        <td><?= __("Action") ?></td>
        <td></td>
      </tr>
         <?php for( $i = 0; $i < count( $links ); $i++) {?>
          <tr class='alternate' style="background:aqua;">
            <?php

            $login = $links[$i]['login_name'];
            $url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;
/*
            $current_url = PA::$url .'/' .FILE_USER_BLOG .'?uid='.$links[$i]['user_id'];
            $url_perms = array('current_url' => $current_url,
                                      'login' => $login
                                    );
            $url = get_url(FILE_USER_BLOG, $url_perms);
*/
            $user_url = $url;
            ?>
            <td><input type="checkbox" name="uid[]" value="<?php echo $links[$i]['user_id']; ?>" /></td>
            <td><a href="<?php echo $user_url;?>">
                  <?= uihelper_resize_mk_user_img($links[$i]['picture'], 35, 35, 'alt="facewall"') ?>
                </a>
            </td>
            <td><?php echo PA::date($links[$i]['created'] ,'short') // date('Y-M-d', $links[$i]['created']);?></td>
            <td><a href="<?php echo $user_url;?>"><?php echo $links[$i]['login_name'];?></a></td>
            <td><?php echo chop_string($links[$i]['first_name'].' '.$links[$i]['last_name'], 25);?>   </td>
            <td><a href="mailto:<?php echo $links[$i]['email'];?>"><?php echo $links[$i]['email'];?></a></td>
            <td><a href="<?php echo PA::$url .'/moderate_users.php?action=approve&uid='.$links[$i]['user_id'];?>">Approve</a></td>
            <td><a href="<?php echo PA::$url .'/moderate_users.php?action=deny&uid='.$links[$i]['user_id'];?>" class='delete'>Deny</a></td>
          </tr>
        <?php } ?>
        <tr>
            <td colspan="3">
              <select name="action" id="act">
                <option value="">--- Select ---</option>
                <option value="multiple_approve">Approve</option>
                <option value="multiple_deny">Deny</option>
              </select> Selected <input type="submit" name="submit" value="Go">
            </td>
            <td colspan="4"></td>
          </tr>
          <tr>
            <td colspan="7"><input type="checkbox" name="check_uncheck" onclick='javascript: check_uncheck_all("manage_users", "check_uncheck");'>(un)check all</td>
          </tr>
      </table>

    <?php }else {?>
    <div class ="required"><?= __("No User.") ?></div>
    <?php }?>

<?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>

  </fieldset>
</form>
