<?php
  //Need to uncomment this one when we will rewrite the url to dynamic pages in redirect_rules.inc
  //$form_action = PA::$url.'/'.FILE_CHANGE_PASSWORD.'?log_nam='.PA::$page_user->login_name;
  $form_action = PA::$url.'/'.FILE_DYNAMIC.'?page_id=54&amp;log_nam='.PA::$page_user->login_name;  
  $form_action .= '&amp;uid='.PA::$page_uid;
  $form_action .= '&amp;forgot_password_id='.$forgot_password_id;
?>
<div id="image_gallery_upload">
   <form action="<?php echo $form_action?>" method="post">
    <fieldset class="center_box">
      <legend><?php echo __("Welcome") ?> <?php echo PA::$page_user->first_name.' '.PA::$page_user->last_name?></legend>
       <div class="field_medium">
            <h5><label for="Login name"><?php echo __("Enter your new password") ?>:</label></h5>
            <input type="password" name="password"  class="text longer" id="login_name" />
       </div>
       <div class="field_medium">
            <h5><label for="first name"><?php echo __("Re-enter the new password") ?>:</label></h5>
            <input type="password"  class="text longer" id="f_name" name="confirm_password" />
       </div>
          
       <div class="button_position">   
       <input type="submit" name="submit" value="Change password">
       <input type="hidden" name="forgot_password_id" value="<?php echo $forgot_password_id?>">
       <input type="hidden" name="action" value="ChangePassword">
       </div>
    </fieldset>
  </form>
</div>