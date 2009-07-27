<?php  
  $field_name = 'user_type';
  $value = DISABLED_MEMBER;
  if( $super_user_and_mothership ) {
    $field_name = 'is_active';
    $value = DISABLED;
  }

  $roles_list = array();

  if (!empty($link_role)) {
     foreach( $link_role  as $a_role) {
       $roles_list[$a_role['id']] = $a_role['name'];
     }
  }

?>

<div class="description"><?= __("Manage users registered on your network") ?></div>
<form action="<?php echo PA::$url .$_SERVER['REQUEST_URI']?>" class="inputrow">
  <fieldset class="center_box">
    <legend><?= __("Search Registered Users") ?></legend><input name="keyword" value="<?php echo htmlspecialchars(@$_GET['keyword']); ?>" type="text" size="18" />
   <input name="search" type="submit" id="search" value="Search" />
   <a href="<?php echo PA::$url .'/manage_user.php?sort_by=alphabetic'?>"><?= __("Alphabetical") ?></a>
   <a href="<?php echo PA::$url .'/manage_user.php?sort_by=created'?>"><?= __("Date Created") ?></a>
  </fieldset>
</form>

<div class="display_false" name="assign_role" id="assign_role"></div>


<form name="manage_users" method="post" action="<?php echo PA::$url .$_SERVER['REQUEST_URI']?>">
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
     <table cellpadding="2" cellspacing="2">
      <tr>
        <td>Select</td>
        <td>Picture</td>
        <td><a href="<?php echo PA::$url .'/manage_user.php?sort_by=created&search=Search&keyword='.@$_GET['keyword']?>"><?= __("Registered") ?></a></td>
        <td><a href="<?php echo PA::$url .'/manage_user.php?sort_by=alphabetic&search=Search&keyword='.@$_GET['keyword']?>"><?= __("User Name") ?></a></td>
        <td>Name</td>
        <td>Email</td>
        <td>User Roles</td>
        <td colspan = "3" align="center"><?= __("Action") ?></td>
        <td></td>
      </tr>
         <?php for( $i = 0; $i < count( $links ); $i++) {?>
          <tr class='alternate' style="background:aqua;">
            <?php
              $search_str = (!empty($_GET['keyword'])) ? '&keyword='.$_GET['keyword'].'&search=Search' : null;
              $sorting = (!empty($_GET['sort_by'])) ? '&sort_by='.$_GET['sort_by'] : null;

              if ( $links[$i][$field_name] == $value ) {
                $status = '<span class="required">'.__("Enable").'</span>';
                if(!empty($_GET['page'])){
                  $url=PA::$url .'/manage_user.php?action=enable&uid='.$links[$i]['user_id'].'&page='.$_GET['page'].$search_str.$sorting;
                } else {
                  $url=PA::$url .'/manage_user.php?action=enable&uid='.$links[$i]['user_id'].$search_str.$sorting;
                }

              } else {
                $status = __('Disable');
                if(!empty($_GET['page'])) {
                  $url=PA::$url .'/manage_user.php?action=disable&uid='.$links[$i]['user_id'].'&page='.$_GET['page'].$search_str.$sorting;
                } else {
                  $url=PA::$url .'/manage_user.php?action=disable&uid='.$links[$i]['user_id'].$search_str.$sorting;
                }


              }
              $delete_url = PA::$url .'/delete_user.php?uid='.$links[$i]['user_id'];
              $login = $links[$i]['login_name'];
/*
              $current_url = PA::$url .'/'.FILE_USER_BLOG .'?uid='.$links[$i]['user_id'];
              $url_perms = array('current_url' => $current_url,
                                  'login' => $login
                                );
              $user_url = get_url(FILE_USER_BLOG, $url_perms);
*/
              $user_url = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $login;
            ?>
            <td><input type="checkbox" name="uid[]" value="<?php echo $links[$i]['user_id']; ?>" /></td>
            <td><a href="<?php echo $user_url;?>">
                  <?= uihelper_resize_mk_user_img($links[$i]['picture'], 35, 35, 'alt="facewall"') ?>
                </a>
            </td>
            <td><?php echo PA::date($links[$i]['created'] ,'short') // date('Y-M-d', $links[$i]['created']);?></td>
            <td><a href="<?php echo $user_url;?>"><?php echo $links[$i]['login_name'];?></a></td>
            <td><?php echo wordwrap($links[$i]['first_name'].' '.$links[$i]['last_name'], 20, "<br />\n", true);?></td>
            <td><?php echo wordwrap($links[$i]['email'], 20, "<br />\n", true);?></td>
<!--
            <td><?php echo chop_string($links[$i]['first_name'].' '.$links[$i]['last_name'], 25);?></td>
            <td width="100"><a href="mailto:<?php echo $links[$i]['email'];?>">
             <?php
               $start = 0;
               $length = (strlen($links[$i]['email']) + 15);
							 for($end=0; $end <= $length; ($end+=15)) {
                 echo(substr($links[$i]['email'], $start, $end)."<br>");
                  $start = $end;
                }?></a>
            </td>
-->
            <td><div  id = "curr_role<?=$links[$i]['user_id'];?>" >
             <?php
              $user_roles = Roles::get_user_roles((int)$links[$i]['user_id'], DB_FETCHMODE_ASSOC);
 //             echo '<pre>' . print_r($user_roles,1) . '</pre>';
              foreach($user_roles  as $role) {
                 $rolename = $roles_list[$role['role_id']];
             ?>
             <?=$rolename;?><br />
           <?php } ?>
           <div></td>

            <td><a href="<?php echo $url;?>"><?php echo $status;?></a>
              <?php if($links[$i]['user_type'] == 'waiting_member') {
               $appr_url=PA::$url .'/manage_user.php?action=approve&uid='.$links[$i]['user_id']; ?>
               <br><font color="red"><a href="<?=$appr_url?>"><?php echo '<font color="red">' .__('Approve').'</font>' ?></a>
               <? } ?>
            </td>
            <td><a href="<?php echo $delete_url;?>" onclick="javascript: return delete_confirmation_msg('<?= __("Are you sure you want to delete this user?") ?>');" class='delete'><?= __("Delete") ?></a><br />
              <?php if($links[$i]['user_type'] == 'waiting_member') echo '<font color="red">'.__('pending').'</font>'; ?>
            </td>
            <td ><a href='javascript: roles.showhide_roleblock("assign_role","<?php echo $links[$i]['user_id']; ?>", "-1");' onclick='javascript: roles.showhide_roleblock("assign_role","<?php echo $links[$i]['user_id']; ?>", "-1");'><?= __("Assign Role") ?></a></td>
          </tr>
        <?php } ?>
        <tr>
            <td colspan="3">
               <select name="action" id="act">
                <option value="">--- <?= __("Select") ?> ---</option>
                <option value="approve"><?= __("Approve") ?></option>
                <option value="disable"><?= __("Disable") ?></option>
                <option value="enable"><?= __("Enable") ?></option>
                <option value="delete"><?= __("Delete") ?></option>
              </select> <?= __("Selected") ?> <input type="submit" name="submit" value="<?= __("Go") ?>" onclick="javascript: if(document.getElementById('act').selectedIndex == 4) { if(confirm('<?= __("Are you sure?") ?>')) return true; else return false;} return true;">
            </td>
            <td colspan="4"></td>
          </tr>
          <tr>
            <td colspan="7"><input type="checkbox" name="check_uncheck" onclick='javascript: check_uncheck_all("manage_users", "check_uncheck");'><?= __("(un)check all") ?></td>
          </tr>
      </table>

    <?php }else {?>
    <div class ="required"><?= __("No users") ?></div>
    <?php }?>

<?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links;?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>

  </fieldset>
<?php echo $config_navigation_url; ?>
</form>

<script>


</script>
