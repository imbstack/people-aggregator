<br/>
<form method="post" action="<?php echo PA::$url."/".FILE_DYNAMIC?>?page_id=<?=PAGE_POLL?>&action=SelectPollModuleSubmit"   enctype="multipart/form-data" id = "myform" name="myform" onsubmit="javascript:return create_poll_form_validation();return validate_form();">
<fieldset class="center_box">
<div class="field_medium">
      <h4><label for="title"><span class="required"> * </span><?= __("Poll Topic") ?>:</label></h4>
      <input type="text" class="text longer" name="topic" id= "topic"/>
      <div class="field_text"></div>
</div>
<div class="field_medium">
  <h4><label for="option"><span class="required"> * </span><?= __("Number Of Options") ?>:</label></h4>
  <select name="num_option" onchange="javascript: ajax_method_poll_options();" id="num_pollid">
  	<option value=""></option>
    <?php for($i=2;$i<=8;$i++) {?>
      <option value="<?=$i?>"><?php echo $i;?></option>
    <?php }?>
  </select>
</div>
<div id="show_options"></div>

</fieldset>
</form>