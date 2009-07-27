<?php
  if( !$is_error ) {
    $extra = unserialize(PA::$network_info->extra);
    $relation = $extra['relationship_options'];

    $show_mode = 1;
    if(isset($extra['relationship_show_mode']['value'])) {
      $show_mode = $extra['relationship_show_mode']['value'];
    }

    $checked = null;
    if ($in_family) {
      $checked = ' checked="checked"';
    }
?> 
 <div id="edit_relations">
  <form name="edit_relationship" method="post" action="<? echo PA::$url.PA_ROUTE_EDIT_RELATIONS.'/uid='.$relation_uid?>">
    <?php if($show_mode == 2) : ?>
      <p><?= __("Specify how close you are in your relationship with") ?> <a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $relation_uid?>"><?php echo $display_name?></a></p>
    <?php endif; ?>
    <p><a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $relation_uid?>">
          <?php echo uihelper_resize_mk_user_img($relation_picture, 185, 180, 'alt="User Picture"'); ?>
        </a><br />
        <?= __('User: ') . $display_name ?>
    </p>
    <?php if($show_mode == 2) : ?>
      <p><input type="checkbox" name="in_family" id="in_family" value="1"<?php echo $checked;?> /> <?= __("Check if") ?> <a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $relation_uid?>"><?php echo $login_name?></a> <?= __("is") ?> <b><?= __("in family") ?></b>. </p>
    <?php endif; ?>
    <div id="relation_type" style="display:<?= ($show_mode == 2) ? 'block' : 'none' ?>">
      <ul>
        <li><img src="<?php echo PA::$theme_url . '/images'?>/havent-met.jpg" alt="havent met" /> <h2><?php echo $relation['most_distant_relation']['value'] ;?></h2> <input id="level5" name="level" value="5" <?php if ($relationship_level == 5)  { ?> checked="checked" <?php } ?> type="radio"></li>
        <li><img src="<?php echo PA::$theme_url . '/images'?>/acquaintance.jpg" alt="acquaintance" /> <h2><?php echo $relation['distant_relation']['value'] ;?></h2> <input id="level1" name="level" value="1" <?php if ($relationship_level == 1)  { ?> checked="checked" <?php } ?> type="radio"></li>
        <li><img src="<?php echo PA::$theme_url . '/images'?>/friend.jpg" alt="friend" /> <h2><?php echo $relation['relation']['value'] ;?></h2> <input id="level2" name="level" value="2" <?php if ($relationship_level == 2)  { ?> checked="checked" <?php } else if (empty($relationship_level)) { ?> checked="checked" <?php } ?> type="radio"></li>
        <li><img src="<?php echo PA::$theme_url . '/images'?>/good-friend.jpg" alt="good friend" /> <h2><?php echo $relation['close_relation']['value'] ;?></h2> <input id="level3" name="level" <?php if ($relationship_level == 3)  { ?> checked="checked" <?php } ?> value="3" type="radio"></li>
        <li><img src="<?php echo PA::$theme_url . '/images'?>/best-friend.jpg" alt="best friend" /> <h2><?php echo $relation['closest_relation']['value'] ;?></h2> <input id="level4" name="level" <?php if ($relationship_level == 4)  { ?> checked="checked" <?php } ?> value="4" type="radio"></li>
      </ul>
    </div>
    <input type='hidden' name='action' value='EditRelation'>
    <input type='hidden' name='do' value="<?php echo (isset($_GET['do'])) ? $_GET['do'] : NULL ?>" />
    <p>
     <input type="submit" name="submit" class="hand" value="<?= ($status == PENDING) ? __('Update Relationship Request') : __('ADD as a Friend') ?>" />
    </p>
   </form>
 </div>
<?php } ?>
