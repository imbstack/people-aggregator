<?php 
$counter = count($topic);
if (!empty($counter) && is_object($topic[0])) {
?>
<form enctype="multipart/form-data" action="<?php echo PA::$url."/".FILE_DYNAMIC?>?page_id=<?=PAGE_POLL?>&action=SelectPollModuleSubmit" method="post" onsubmit="return validate_form();">
<?php
if ($topic[0]->group_id != 0) {
	echo "<input type='hidden' name='gid' value='".htmlspecialchars($topic[0]->group_id)."' />";
}
?>
<fieldset class="center_box">
  <span style="font-size:16px;font-weight:bold;"><?php echo __('Select Any One Topic') ;?>:-</span>
  <?php
      for ($i=0; $i < $counter; $i++) {
        echo '<div class="field_medium" style="height:100%;font-size:14px;">';
        echo '<span style="font-size:14px;font-weight:bold;"><br/>'.$topic[$i]->title.'</span>&nbsp;';
        if ($topic[$i]->poll_id != @$current_poll[0]->poll_id) {
          echo "<input type ='radio' name='poll' value='$poll_id[$i]' />"."<br/><br/>";
          echo '<a href ="' . PA::$url . '/' .FILE_DYNAMIC . '?page_id=' . PAGE_POLL . '&action=delete&id=' . $topic[$i]->poll_id . '&cid=' . $topic[$i]->content_id . '&gid=' . $topic[$i]->group_id . '">Delete this poll </a>';
        }
        echo '<br/>'.'options:-'.'<br />';
        $count = count($options[$i]);
        for ($j = 1; $j <= $count; $j++) {
          echo $options[$i]['option'.$j];
          echo '<br/>';
        }
       echo '<div class="field_text"></div></div>'; 
      } 
     echo '<input type="hidden" name="prev_poll_id" value="'.@$current_poll[0]->poll_id.'">'; 
     echo '<input type="hidden" name="prev_poll_changed" value="'.@$current_poll[0]->changed.'">';   
     echo '<br/>'.'<input type="submit" value="submit" name="submit" class="giant_input_btn">'.'<br/>';
     echo "<input type=\"hidden\" name=\"type\" value=\"select_poll\" /><br /> ";
  ?>
</fieldset>  
</form>
<?php } else {?>
  <div style="width:100%;text-align:center;margin-top:10px;">
    <b><?= __("No poll was created yet.") ?></b>
  </div>
<?php }?>
