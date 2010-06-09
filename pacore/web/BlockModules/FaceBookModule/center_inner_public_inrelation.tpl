<?php
  global  $login_uid;
  $description = __('There are ') . $total .__(' members ') . $sub_title;
?>
<?php if (count($links) > 0) {?>
<div class='description'><?php echo $description;?></div>
<? } ?>

<div id="GroupsDirectoryModule">
<?php if( !empty( $page_links ) ) { ?>
  <div class="prev_next">
    <?php echo $page_links; ?>
  </div>
<?php } ?>

<ul class="members_list">
 <? if (!empty($links)) { ?>
<?php
  for( $counter = 0; $counter < count( $links ); $counter++ ) {
      $user_link = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $links[$counter]['user_id'];
?>
<li>
  <a href="<?php echo $user_link;?>">
    <?php echo uihelper_resize_mk_user_img($links[$counter]['picture'], 50, 50, 'alt="User picture."') ?>

      <div class="description">
        <h2><?php echo chop_string($links[$counter]['display_name'], 18);?></h2>

        <?php
        if ($_GET['view_type'] == 'in_relations') {
          switch( (int)$links[$counter]['relation_type_id'] ) {
            case 1:
              $relation_type = 'Acquaintance';
            break;
            case 2:
              $relation_type = 'Friend';
            break;
            case 3:
              $relation_type = 'Good Friend';
            break;
            case 4:
              $relation_type = 'Best Friend';
            break;
            case 5:
              $relation_type = "Haven't Met";
            break;
          }
        ?>
         <?php echo ''//$relation_type; ?><br />
        <?} ?>
      member since: <?php echo manage_content_date ($links[$counter]['created']); ?>
    </div>
  </a>
  <?php  if ( $links[$counter]['status'] == PENDING) { ?>
  <div>
  <form method = "POST" action="" style="margin:0px">
  <input type="hidden" name="related_id" value="<?php echo $links[$counter]['user_id']; ?>" />
  <input type="hidden" name="related_email" value="<?php echo $links[$counter]['email']; ?>" />
   <table class="rel_status" cellpadding="0" cellspacing="0">
     <tr>
       <td><input type="submit" value="<?= __("Approve") ?>"  name="btn_approve" /></td>
       <td><input type="submit" value="<?= __("Deny") ?>"  name="btn_deny" /></td>
     </tr>
   </table>
  </form>
  </div>
<?php } else { ?>
   <table class="rel_status" cellpadding="0" cellspacing="0">
     <tr>
       <td><?php echo 'Relation type: ' . $relation_type; ?></td>
     </tr>
   </table>
<?php } ?>

 </li>
 <?} // end of For loop?>
 <? } else { ?>
<li> No Relation </li>
 <?}?>
 </ul>
 <?php if( !empty( $page_links ) ) { ?>
  <div class="prev_next">
    <?php echo $page_links; ?>
  </div>
<?php } ?>

 </div>