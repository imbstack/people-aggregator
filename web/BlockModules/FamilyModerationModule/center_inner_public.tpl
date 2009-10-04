<?php
require_once "api/Permissions/PermissionsHandler.class.php";
require_once "web/includes/classes/UrlHelper.class.php";
$lc_group_noun = __("Family");

?>

<style>
 .family_odd {
   background-color: #fff;
 }
 .family_even {
   background-color: #e6e6e6;
 }
</style>

<h1><?= __("Family Members Moderation") ?></h1>
<br />
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
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
      <?php   $links_count = count($links);
              for($i = 0; $i < $links_count ; $i++) {
                if ( $i%2 == 0 ) { $tr_class = "family_even";} else { $tr_class = "family_odd"; }
      ?>
       <tr class="<?=$tr_class?>">
         <td width="9%" rowspan="4" align="center"><input type="checkbox" name="members[]" value="<?= $links[$i]["user_id"]; ?>"></td>
         <td width="12%" rowspan="4"><label><a href="<?= url_for("user_blog", array("login" => $links[$i]['login_name'])) ?>"><?= uihelper_resize_mk_user_img($links[$i]['picture'], 60, 60) ?></a></label></td>
         <td width="49%"><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$i]['user_id'] ?>"><?= $links[$i]['login_name']?></a></td>
         <td width="19%" rowspan="4" ><?= $links[$i]['relType'] ?></td>
         <td rowspan="4">
<!--         
           <div id="buttonbar" style="width: 150px; margin: 0px;">
             <ul>
               <li>
                <?php if(PermissionsHandler::can_group_user(PA::$login_uid, $group_id, array('permissions' => 'manage_roles'))) : ?>
                  <a href='javascript: roles.showhide_roleblock("assign_role","<?= $links[$i]['user_id']; ?>", "<?= $group_id ?>");' onclick='javascript: roles.showhide_roleblock("assign_role","<?= $links[$i]['user_id']; ?>", "<?= $family_id ?>");'><?= __("Edit/Assign Role") ?></a>
                <?php endif; ?>
               </li>
             </ul>
           </div>
-->           
         </td>
      </tr>
      <tr class="<?=$tr_class?>">
         <td><?= $links[$i]['first_name']." ".$links[$i]['last_name']?></td>
      </tr>
      <tr class="<?=$tr_class?>">
         <td><?= $links[$i]['email']?></td>
      </tr>
      <tr class="<?=$tr_class?>">
         <td><?= __("Joined Family") ?>: <?= PA::date($links[$i]['created'] ,'long') ?></td>
      </tr>
      <? } ?>
      <input type="hidden" name="user_id" id="user_id" value="">
      <input type="hidden" name="user_status" id="user_status" value="">
      <input type="hidden" name="family_id" value="<?= $family_id; ?>">
      <input type="hidden" name="view" value="<?= $div_visible_for_moderation ?>">
      <input type="hidden" name="gid" value="<?= $family_id ?>">
      <input type="hidden" name="action" id="action" value="deleteFamilyMembers">

     </table>
     <table width="100%" cellpadding="0" cellspacing="5" border="0">
       <tr>
         <td>
           <div class="buttonbar" style="width: 150px; margin: 0px;">
             <ul>
               <li>
                 <a href='#' onclick="javascript: if(confirm('Are you sure?')) { document.forms['formAllMembers'].submit(); return true; } else {return false;}"><?= __("Remove Selected")  ?></a>
               </li>
               <li>
                 <a href='<?= UrlHelper::url_for(PA_ROUTE_FAMILY_MODERATION, array('gid' => $family_id, 'action' => 'addChild' )) ?>'><?= __("Add a Child")  ?></a>
               </li>
             </ul>
           </div>  
         </td>  
       </tr>
    </table>
    <?php }  else { echo sprintf(__("There are no members in this %s"), $lc_group_noun);}?>

   </form>
 </div>
