<?php global $current_theme_path;?>

<ul id="filters">
  <li><a href="<?php echo PA::$url . PA_ROUTE_GROUP_MODERATION ?>/view=members&gid=<?php echo $group_id;?>"><?= __("Moderate Group Members") ?></a></li>
  <li><a href="<?php echo PA::$url . PA_ROUTE_GROUP_MODERATION ?>/view=users&gid=<?php echo $group_id;?>"><?= __("Moderate Membership") ?></a></li>
  <li class="active"><a href="<?php echo PA::$url . PA_ROUTE_GROUP_MODERATION ?>/view=content&gid=<?php echo $group_id;?>"><?= __("Moderate Content") ?></a></li>
</ul>

<h1><?= __("Content") ?></h1>
<?php if( $page_links ) {?>
  <div class="prev_next">
  <?php if ($page_first) { echo $page_first; } ?>
     <?php echo $page_links; ?>
  <?php if ($page_last) { echo $page_last;} ?>
  </div>
<?php } ?>
<div id="moderation_content">
  <form name="formPendingContentModeration" method="post">
  
       
     <?php if ($links) {?>
     <table width="100%" cellpadding="0" cellspacing="0">
     <tr>
       <td width="7%" height="40"></td>
       <td width="15%"><label>
       <input type="submit" id="approv" name="btn_approve_content" value="Approve" class="group_moderation_btn">
       </label></td>
       <td width="78%"><input type="submit" id="deny" name="btn_deny_content" value="Deny" class="group_moderation_btn"></td>
     </tr>
   </table>
     <?php   for($i = 0; $i < count($links); $i++) { 
       if ( $i%2 == 0 ) { $color = "#e6e6e6";} else { $color="#fff"; }
     ?>
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr bgcolor=<?=$color?> >
          <td width="10%" height="40" align="center"><input type="checkbox" name="contentIdArray[]" value="<?php echo $links[$i]["content_id"]?>"></td>
          <td width="40%"><label><a href="<?= PA::$url . PA_ROUTE_CONTENT . '/cid=' . $links[$i]["content_id"] ?>&ccid=<?=$_REQUEST['gid'];?>"><?php echo $links[$i]["title"]?></a></label></td>
          <td width="50%"><?php echo $links[$i]["created"]?></td>
        </tr>
      </table>
    
    <? } ?>  
    <table width="100%" cellpadding="0" cellspacing="0">
     <tr>
       <td width="7%" height="40" ></td>
       <td width="15%"><label>
         <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
         <input type="hidden" name="view" value="<?= $div_visible_for_moderation ?>">
         <input type="hidden" name="gid" value="<?= $group_id ?>">
         <input type="hidden" name="action" value="approveContent">
         <input type="submit" id="approv3" name="btn_approve_content" value="Approve" class="group_moderation_btn">
       </label></td>
       <td width="78%"><input type="submit" id="deny3" name="btn_deny_content" value="Deny" class="group_moderation_btn"></td>
     </tr>
   </table>      
    <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
    <?php }  else { echo __("There are no pending moderation requests");}?> 
       
  
   </form>
</div>