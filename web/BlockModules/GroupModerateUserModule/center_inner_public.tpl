<?php global $current_theme_path;?>
<ul id="filters">
  <li><a href="<?php echo PA::$url .PA_ROUTE_GROUP_MODERATION ?>/view=members&gid=<?php echo $group_id;?>"><?= __("Moderate Group Members") ?></a></li>
  <li class="active"><a href="<?php echo PA::$url .PA_ROUTE_GROUP_MODERATION ?>/view=users&gid=<?php echo $group_id;?>"><?= __("Moderate Membership") ?></a></li>
  <li><a href="<?php echo PA::$url .PA_ROUTE_GROUP_MODERATION ?>/view=content&gid=<?php echo $group_id;?>"><?= __("Moderate Content") ?></a></li>
</ul>

<h1><?= __("Requests") ?></h1>
<?php if( $page_links ) {?>
  <div class="prev_next">
  <?php if ($page_first) { echo $page_first; } ?>
     <?php echo $page_links; ?>
  <?php if ($page_last) { echo $page_last;} ?>
  </div>
<?php } ?>

<div id="moderation_content">
  <form name="formPendingModeration" method="post">
   <?php if ($links) {?>
   <table width="100%" cellpadding="5" cellspacing="0" border="0">
     <tr>
       <td colspan="3"><?= __("Approve or deny requests to join this group") ?></td></tr>
       <tr>
       <td width="15%"><label><input type="submit" id="approved_selected" name="btn_approve" value="Approve Selected" class="group_moderation_btn"></label></td>
       <td width="19%"><input type="submit" id="deby_selected2" name="btn_deny" value="Deny Selected" class="group_moderation_btn"></td>
       <td>&nbsp;</td>
       
     </tr>
   </table>
   <?php for($i = 0; $i < count($links); $i++) { 
          if ( $i%2 == 0 ) { $color = "#e6e6e6";} else { $color="#fff"; }
   ?>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <tr bgcolor=<?=$color?> >
         <td width="9%" rowspan="4" align="center"><input type="checkbox" name="selectedArray[]" value="<?php echo $links[$i]['user_id']; ?>"></td>
         <td width="12%" rowspan="3"><a href="<?= url_for("user_blog", array("login" => $links[$i]['login_name'])) ?>"><?= uihelper_resize_mk_user_img($links[$i]['picture'], 60, 60) ?></a></td>
         <td width="79%" height="21"><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$i]['user_id'] ?>"><?php echo $links[$i]['login_name'];?></a></td>
      </tr>
      <tr bgcolor=<?=$color?>>
        <td height="19"><?php echo $links[$i]['first_name']." ".$links[$i]['last_name'];?></td>
      </tr>
      <tr bgcolor=<?=$color?>>
        <td><?php echo $links[$i]['email'];?></td>
      </tr>
    </table>			 
    <? } ?>
    <table width="100%" cellpadding="0" cellspacing="5" border="0">
      <tr>
       
        <td width="22%"><label>
         <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
         <input type="hidden" name="view" value="<?= $div_visible_for_moderation ?>">
         <input type="hidden" name="gid" value="<?= $group_id ?>">
         <input type="hidden" name="action" value="approveUser">
         <input name="btn_approve" type="submit" id="approved_selected2" value="Approve Selected" class="group_moderation_btn"/>
        </label></td>
       
        <td width="19%"><input name="btn_deny" type="submit" id="deby_selected22" value="Deny Selected" class="group_moderation_btn"/></td>
        <td width="53%">&nbsp;</td>
      </tr>
    </table>       
    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
    <?php }  else { echo __("There are no pending moderation requests");}?>
   </form> 
</div>
		