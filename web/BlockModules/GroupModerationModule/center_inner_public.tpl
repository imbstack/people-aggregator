<?php
require_once "api/Permissions/PermissionsHandler.class.php";
$lc_group_noun = strtolower(PA::$group_noun);
?>

<ul id="filters">
  <li class="active"><a href="<?= PA::$url . PA_ROUTE_GROUP_MODERATION ?>/view=members&amp;gid=<?= $group_id?>"><?= sprintf(__("Moderate %s Members"), PA::$group_noun) ?></a></li>
  <li><a href="<?= PA::$url . PA_ROUTE_GROUP_MODERATION ?>/view=users&amp;gid=<?= $group_id?>"><?= __("Moderate Membership") ?></a></li>
  <li><a href="<?= PA::$url . PA_ROUTE_GROUP_MODERATION ?>/view=content&gid=<?= $group_id?>"><?= __("Moderate Content") ?></a></li>
</ul>

<h1><?= __("Moderation") ?></h1>
<?php if ($page_links) { ?>
  <div class="prev_next">
  <?php if ($page_first) { echo $page_first; } ?>
     <?= $page_links; ?>
  <?php if ($page_last) { echo $page_last;} ?>
  </div>
<?php } ?>

<div id="moderation_content">
   <form name="formAllMembers" method="post">
   <div class="display_false" name="assign_role" id="assign_role"></div>

      <?php if ($links) {?>
      <table width="100%" cellpadding="0" cellspacing="5" border="0">
	<tr><td colspan="3"><?= sprintf(__("Remove selected members from the %s"), $lc_group_noun) ?></td></tr>
	<tr>
          <td width="9%"></td>
          <td width="12%"><label><input name="Remove" type="submit" id="Remove" value="Remove" class="group_moderation_btn"/></label></td>
          <td width="79%">&nbsp;</td>
	</tr>
      </table>
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <?php   $links_count = count($links);
              for($i = 0; $i < $links_count ; $i++) {
                if ( $i%2 == 0 ) { $color = "#e6e6e6";} else { $color="#ffffff"; }
      ?>
       <tr bgcolor=<?=$color?>>
         <td width="9%" rowspan="4" align="center"><input type="checkbox" name="members[]" value="<?= $links[$i]["user_id"]; ?>"></td>
         <td width="12%" rowspan="4"><label><a href="<?= url_for("user_blog", array("login" => $links[$i]['login_name'])) ?>"><?= uihelper_resize_mk_user_img($links[$i]['picture'], 60, 60) ?></a></label></td>
         <td width="49%"><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$i]['user_id'] ?>"><?= $links[$i]['login_name']?></a></td>
         <td width="19%" rowspan="4" ><?= $links[$i]['user_type'] ?></td>
         <td rowspan="4">
           <div id="buttonbar" style="width: 150px; margin: 0px;">
             <ul>
               <li>
<!--
                 <a href="#" onclick="javascript: document.getElementById('user_status').value='<?= ($links[$i]['user_type']=='member') ? 'moderator' : 'member' ?>'; document.getElementById('user_id').value=<?= $links[$i]['user_id'] ?>;document.getElementById('action').value='changeStatus'; document.forms['formAllMembers'].submit();">
                   <?= ($links[$i]['user_type'] == 'member') ? __('Declare as Moderator') : __('Declare as Member') ?>
                 </a>
-->
                <?php if(PermissionsHandler::can_group_user(PA::$login_uid, $group_id, array('permissions' => 'manage_roles'))) : ?>
                  <a href='javascript: roles.showhide_roleblock("assign_role","<?= $links[$i]['user_id']; ?>", "<?= $group_id ?>");' onclick='javascript: roles.showhide_roleblock("assign_role","<?= $links[$i]['user_id']; ?>", "<?= $group_id ?>");'><?= __("Edit/Assign Role") ?></a>
                <?php endif; ?>
               </li>
             </ul>
           </div>
         </td>
      </tr>
              <tr bgcolor=<?=$color?>>
                <td><?= $links[$i]['first_name']." ".$links[$i]['last_name']?></td>
              </tr>
              <tr bgcolor=<?=$color?>>
                <td><?= $links[$i]['email']?></td>
              </tr>
              <tr bgcolor=<?=$color?>>
                <td>Joined Group: <?= PA::date($links[$i]['created'] ,'long') // date("M d, Y",$links[$i]["created"])?></td>
              </tr>
      <? } ?>
      <input type="hidden" name="user_id" id="user_id" value="">
      <input type="hidden" name="user_status" id="user_status" value="">
      <input type="hidden" name="group_id" value="<?= $group_id; ?>">
      <input type="hidden" name="view" value="<?= $div_visible_for_moderation ?>">
      <input type="hidden" name="gid" value="<?= $group_id ?>">
      <input type="hidden" name="action" id="action" value="deleteMembers">

     </table>
     <table width="100%" cellpadding="0" cellspacing="5">
       <tr>
        <td width="9%"></td>
        <td width="12%"><label><input name="Remove" type="submit" id="Remove" value="Remove" class="group_moderation_btn"/></label></td>
        <td width="79%"></td>
       </tr>
     </table>
      <?php }  else { echo sprintf(__("There are no members in this %s"), $lc_group_noun);}?>

   </form>
 </div>
