<?php
  if (!empty($gid)) {
    $description = sprintf(__('There are %d members in this %s.'), $total, strtolower(PA::$group_noun));
  } else if ( !isset($_GET['uid']) ) {
    if ( !isset(PA::$login_uid) || $view_type != 'relations') {
    $description = sprintf(__('There are %d members.'), $total);
    } else {
    $description = sprintf(__('There are %d friends for %s.'), $total, $user_name);
    }
  } else {
    $description = sprintf(__('There are %d friends for %s.'), $total, $user_name);
  }
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
    foreach($links as $link) {
      $user_link = PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $link['user_id'];
?>
<li><a href="<?php echo $user_link;?>">
      <?php echo uihelper_resize_mk_user_img($link['picture'], 50, 50, 'alt="User picture."') ?>
      <div class="description" style="text-align: center">
      <h2><?php echo chop_string($link['display_name'], 18);?></h2>
      <?php
      if (!empty(PA::$config->useTypedGroups) && !empty($link['membertype'])) {
      ?>
      <b><?=$link['membertype']?></b>
      <?php
      }
      ?>
      <?php
        if (($view_type == 'relations') || ($view_type == 'in_relations')) {
          switch( (int)$link['relation_type_id'] ) {
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
       <br />
       <?= __("member since:") ?> <?php echo manage_content_date ($link['created']); ?>
       <? } ?>
      </div>
   </a>
  <?php if(($view_type == 'relations') || ($view_type == 'in_relations')) : ?>
  <?php  if(($link['status'] == 'pending') && (!key_exists('network', $link))) { ?>
    <div>
      <form method = "POST" action="" style="margin:0px">
        <input type="hidden" name="related_id" value="<?php echo $link['user_id']; ?>" />
        <input type="hidden" name="related_email" value="<?php echo $link['email']; ?>" />
        <table class="rel_status" cellpadding="0" cellspacing="0">
        <tr>
          <?php if (PA::$login_uid == PA::$page_uid) : ?>
            <td><input type="submit" value="<?= __("Approve") ?>"  name="btn_approve" /></td>
            <td><input type="submit" value="<?= __("Deny") ?>"  name="btn_deny" /></td>
          <?php else: ?>
            <td><a href="#"><?php echo __("waiting approval") ?></a></td>
          <?php endif; ?>
        </tr>
        </table>
      </form>
    </div>
  <?php } else if(($link['status'] == 'pending') && (key_exists('network', $link))) { ?>
       <table class="rel_status" cellpadding="0" cellspacing="0">
       <tr>
          <td><a href="#"><?php echo $link['status']; ?></a></td>
       </tr>
       </table>
  <?php } else { ?>
       <table class="rel_status" cellpadding="0" cellspacing="0">
       <tr>
         <td><?php echo 'Relation type: ' . $relation_type; ?></td>
       </tr>
       </table>

  <?php } ?>
  <?php else: ?>
      <table class="rel_status" cellpadding="0" cellspacing="0">
      <tr>
         <td><?= __("member since:") ?> <?php echo manage_content_date ($link['created']); ?></td>
      </tr>
      </table>
  <?php endif; ?>
  </li>
  <? } // End of FOR loop
 } else { // End of if (!empty($links)) ?>
   <li><?= __("No relation exists.") ?></li>
 <? } ?>
</ul>
<?php if( !empty( $page_links ) ) { ?>
  <div class="prev_next">
    <?php echo $page_links; ?>
  </div>
<?php } ?>
</div>