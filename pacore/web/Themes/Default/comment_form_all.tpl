<?php 
  $div_id = (!empty($div_id)) ? $div_id: 'report_abuse_div';
  $abuse_report_type = (!empty($type)) ? "<input type=\"hidden\" name=\"type\" value=$type>": '';
  $abuse_report_id = (!empty($id)) ? "<input type=\"hidden\" name=\"id\" value=$id>": '';
  $display = (empty($display)) ? 'display:none': 'display:block';
  $action = (empty($action)) ? 'submit_comment.php': $action;
  $form_handler_field = (empty($module_name)) ? '': "<input type=\"hidden\" name=\"form_handler\" value=$module_name>";
?>
<div id="<?php echo $div_id;?>" style="<?php echo $display;?>">
<form name='comment_form' action="<?php echo $action;?>" method='post' onsubmit="javascript: return confirm_delete('<?= __("Are you sure you want to post the comment?") ?>');">

<fieldset class="center_box">
<legend><?= __("Leave a Comment") ?></legend>
  <div class="field_bigger">
    <label ><span class="required"> * </span> <?= __("Comment") ?></label>
    <textarea name="comment" cols="55" rows="5" id="Content"></textarea>
  </div>

<div class="button_position">
  <input type='submit' name='submit' value='<?= __("Submit Comment") ?>' />
  <?php echo $abuse_report_id;?>
  <?php echo $abuse_report_type;?>
  <?php echo $form_handler_field;?>
</div>
</fieldset>
</form>
</div>