<?php
?>

<form name='comment_form' action="" method='post' onsubmit="javascript: return confirm_delete('<?= __("Are you sure you want  to post the comment?") ?>');">
<fieldset class="center_box">

<legend><?= __("Leave a Comment") ?></legend>
   <?php
      $form_fields = NULL;
      if( PA::$login_uid ) {
    ?>
      <h3><a href="<?php echo PA::$url . PA_ROUTE_USER_PUBLIC . '/' . PA::$login_uid ?>"><?php echo PA::$login_user->display_name ?></a> said</h3>
    <?php
      }
      else {
        $form_fields .='<div class="field"><h5><span class="required"> * </span><label for="name">Name</label></h5><input type="text" name="name"  class="text normal" /></div>';
        $form_fields .='<div class="field"><h5><span class="required"> * </span><label for="email">Email</label></h5><input type="text" name="email"  class="text normal" />
</div>';
        $form_fields .='<div class="field"><h5><label for="homepage"><?= __("Homepage") ?></label></h5>
<input type="text" name="homepage"  class="text normal" /></div>';
      }
    ?>

      <?php echo $form_fields; ?>
      <div class="field_big">
<h5><label ><span class="required"> * </span> <?= __("Comment") ?></label></h5>
<textarea name="comment" cols="55" rows="5" id="Content"></textarea>
</div>
    
</fieldset>
      <div class="button_position">
      <input type='submit' name='addcomment' value='<?= __("Submit Comment") ?>' />
      <input type='hidden' name='cid' value="<?php echo $cid;?>" />
      <input type='hidden' name='ccid' value="<?php echo @$ccid;?>" />
      <input type='hidden' name='action' value="submitComment" />
  </div>
  </form>
  

