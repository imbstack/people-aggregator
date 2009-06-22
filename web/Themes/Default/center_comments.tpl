<?php
require_once "api/Permissions/PermissionsHandler.class.php";
  global $current_theme_path, $network_info, $login_uid;
  $param_array = array('permissions' => 'view_abuse_report_form');
  $total_comments = count( $comments );
  if( $total_comments ) {
?>
  <?php
  foreach( $comments as $comment ) {
    // output filtering
    $comment['comment'] = _out($comment['comment']);
  ?>
  <tr valign="top" id='comment_<?php echo $comment['comment_id'];?>'>
  <td class="author">

      <?php
        if( $comment['user_id'] == -1 ) {
      ?>
      <a rel="nofollow" href="<?php echo htmlspecialchars($comment['homepage'])?>"><?php echo $comment['name']?></a> said:
      <?php
        }
        else {
        echo '<a href="'.PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $comment['user_id'].'">'.uihelper_resize_mk_user_img($comment['picture'], 80, 80, 'alt=""').'</a>';
      ?>

      <br /><a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $comment['user_id']?>"> <?php echo $comment['name']?></a>
      <?php } ?>
  </td>
    <td class="message">
   <?php echo nl2br(stripslashes($comment['comment']));?>
    <div class="post_info"><a href="#comment_<?php echo $comment['comment_id'];?>"><?=PA::datetime($comment['created']);?></a>
      <?php
        $params = array('comment_info'=>array('user_id'=>$comment['user_id'], 'content_id'=>$comment['content_id']), 'permissions'=>'delete_comment');
        echo '</div>';
        if (!empty($login_uid)) {
          echo '<div id="buttonbar"><ul>';
          if(PermissionsHandler::can_user(PA::$login_uid, $params)) {
            echo '<li><a href="'.PA::$url .'/deletecomment.php?comment_id='.$comment['comment_id'].'" onclick="return confirm_delete(\'Are you sure you want to delete this comment ? \');">Delete</a></li>';
          }
          if(PermissionsHandler::can_user(PA::$login_uid, $param_array) && ($comment['user_id'] != PA::$login_uid)) {
          echo '<li><a href="javascript: return void();" onclick = showhide_block("report_abuse_div_'.$comment['comment_id'].'"); >Report abuse </a></li>';
          }
          echo '</ul></div>';
        }
      ?>
   </td>
  </tr>
  <tr><td colspan="2"
   <?php
     $id = $comment['comment_id'];
     $param['type'] = 'comment';
     $param['div_id'] = "report_abuse_div_$id";
     $param['id'] = $comment['comment_id'];
     echo uihelper_create_abuse_from($param);
    ?>
    </td></tr>
  <?php

    }
  ?>
<?php
  }
?>
