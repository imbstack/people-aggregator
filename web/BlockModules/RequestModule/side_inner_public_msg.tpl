<form name='requestform' action="<?php echo PA::$url.'/'.FILE_DYNAMIC?>?page_id=<?=PAGE_REQUEST?>&action=RequestModuleSubmit" method='post'>
<div id="register">
   <div id="class_description">
         <br />
          <?php
            if (!empty($error)) {
              echo $error_msg;
            } else if (!empty($success)) {
              echo $success_msg;
            }
          ?>
        </div>
       <div class="button_position">
      <input type="submit" name="back" value="<?= __("Return to home network") ?>" />
    </div>
</form>
</div>