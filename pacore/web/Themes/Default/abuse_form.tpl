<?php 
  $div_id = (!empty($div_id)) ? $div_id: 'report_abuse_div';
  $abuse_report_type = (!empty($type)) ? "<input type=\"hidden\" name=\"type\" value=$type />": '';
  $abuse_report_id = (!empty($id)) ? "<input type=\"hidden\" name=\"id\" value=$id />": '';
?>
<form name='abuse_form' action="" method='post' >
  <div id='<?php echo $div_id;?>' style="display:none">
    <fieldset class="center_box">
    <legend><?= __("Report abuse") ?></legend>
    <div class="field_big">
      <h5><label ><span class="required"> * </span> <?= __("Comment") ?></label></h5>
      <textarea rows="5" cols="67" name="abuse"></textarea>
    </div>
    <?php echo $abuse_report_type;
          echo $abuse_report_id; ?>
    </fieldset><br />
    <div class="button_position">
      <input type='submit' name='rptabuse' value='<?= __("Submit Abuse") ?>' />
      <input type="hidden" name="action" value='submitAbuse' />
    </div>
  </div>
</form>