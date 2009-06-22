<?php 
?>
<div class="module_icon_list">
<ul  class="members">
  <? if (!empty($links)) { 
       // for ($i=0; $i<count($links); $i++) 
       $i=0;
       foreach ($links as $k=>$link) { 
         $class = (( $i%2 ) == 0) ? ' class="color"': NULL;
         $i++;
  ?>
  <li<?php echo $class; ?>>
    <a href="<?=User::url_from_id($link['user_id']);?>">
      <?= uihelper_resize_mk_user_img($link['picture'], 35, 35, 'alt="facewall"') ?>
    <span>
      <b>
       <?=$link['login_name']; ?>
     </b><br />
						<?=$link['no_of_relations'];?> Relations
         </span></a></li>
     <?php } ?>
   <?php  } else { 
            if ($mode == PRI || PA::$login_uid == $uid) {
              $caption = 'No one has added you as a friend. Click <a  href="' . PA::$url . PA_ROUTE_PEOPLES_PAGE . '">here</a> to find friends.';
            } else {
              $user_name = ucfirst(chop_string($user_name, 15));
              $caption = 'No one has added '.$user_name.' as a friend.';
              if(!empty(PA::$login_uid)) {
                $caption .= 'Click <a href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . PA::$page_uid . '/action=add_relation">here</a> to become friends.';
              }
            } 
          ?>
            <li><?php echo __($caption)?></li>
   <?php  }?>
</ul>
</div>