<?php
require_once "api/Permissions/PermissionsHandler.class.php";
  $param_array = array('permissions' => 'view_abuse_report_form');
?>
<div class="post_info">
<?php
      if( $contents->tag_entry )  echo $contents->tag_entry.'<br />';
    ?>
    posted by <a href="<?php echo $user_link?>"><?php echo $user_name?></a> on <?=date("l, F d Y", $contents->created);?>
      <?php
        if( $edit_link ) {
      ?>
      <br/> <div id="buttonbar"><ul><li>  <a href="<?php echo $edit_link?>"> edit </a> </li>
      <?php
        }
      ?>
      <?php
        if( $delete_link) {
      ?>
        <!--&nbsp;--><li> <a href="<?php echo $delete_link?>" onclick="javascript: return delete_content1();"> delete </a> </li></ul></div>
      <?php
        }
     ?>
      <?php
        if( $approval_link) {
      ?>
        <a href="<?php echo $approval_link?>"> approve </a>
      <?php
        }
      ?>
      <?php
        if( $denial_link) {
      ?>
        <a href="<?php echo $denial_link?>"> deny </a>
      <?php
        }
      ?>
     <?php
     if(PermissionsHandler::can_user(PA::$login_uid, $param_array) && ($user_id != PA::$login_uid)) {
     ?>
      <div id="buttonbar">
        <ul>
          <li>
            <a href="javascript: return void();" onclick = "javascript: showhide_block('report_abuse_div');" >Report abuse</a>
          </li>
        </ul>
      </div>
      <?
      }
      ?>

     </div>
    </td>
   </tr>
   <tr><td colspan="2">
   <?php
     echo $abuse_form;
    ?>
    </td></tr>
   <?php
      if($comments) {?>
        <tr>
        <td colspan="2" class="comments_title">
          Comments
        </td>
        </tr>
   <?php echo $comments;
      }
    ?>
    </table>
    <?php
      if ($comment_form) {
        echo $comment_form;
      }
    ?>
