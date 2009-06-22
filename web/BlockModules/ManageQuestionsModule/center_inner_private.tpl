<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

  
  $class = (!empty($edit) || !empty($form_data)) ? 'class="display_true"' :
'class="display_false"';

  if (@$_GET['open'] == 1) {
    $class = 'class="display_true"';
  }

  $legeng_tag = (!empty($edit)) ? __('Edit question') : __('Create new question');
  $html_list = NULL;

  
  $cnt = count($links);
  
  if (!empty($cnt)) {
    $html_list = '<table cellpadding="3" cellspacing="3">
                <tr>
                  <td><b>'.__("select").'</b></td>
                  <td><b>'.__("Question").'</b></td>
                  <td><b>'.__("Delete").'</b></td>
                </tr>';

    for ($i=0; $i< $cnt; $i++) {
     $select = ($selected == $links[$i]['content_id']) ? 'checked': '';         
        
     $html_list .= '<tr><td><input type="radio" name="question_id"
value='.$links[$i]['content_id'].'  '.$select.'/></td>';
     $html_list .= '<td>'.$links[$i]['body'].'</td>';
     $html_list .= '<td><a
href="'.PA::$url .'/manage_questions.php?action=delete&amp;content_id='.$links[
$i]['content_id'].'" onclick="return delete_confirmation_msg(\''.__("Are you sure you want to delete this?").'\') "><?= __("Delete") ?></a></td></tr>';

    }

    $html_list .= '</table>';
  }

?>
<div class="description"><?= __("In this page you can manage questions for your network's users") ?></div>
<form name="formAdCenterManagement" id="formAdCenterManagement" action="" method="POST">
  <fieldset class="center_box">
    <legend><?= __("Manage Questions") ?></legend>
    <?php if ($page_links) {?>
    <div class="prev_next">
      <?php if ($page_first) { echo $page_first; }?>
      <?php echo $page_links?>
      <?php if ($page_last) { echo $page_last;}?>
    </div>
    <?php
      }
    ?> 
    
   <?php echo $html_list; ?>
    
   <div class="button_position">
     <input type="submit" class="buttonbar" name="save" value="<?= __("Set Active Question") ?>"
/> <input type="button" name="btn_new_ads"  class="buttonbar"
value="Add Question"  onclick="javascript: showhide_ad_block('new_ad','<?php
if (!empty($_GET['open'])) {
echo $_GET['open'];}?>', 'manage_questions.php');" />

    </div>   
  </fieldset> 
  
  <div id="new_ad" <?php echo $class; ?>>
    <fieldset class="center_box">
      <legend><?php echo $legeng_tag; ?></legend>
        <div class ="field_bigger">
          <h4><span class="required"> * </span> <?= __("Question") ?>:</h4>
          <textarea rows="5" cols="50" name="body"></textarea>
        </div>
        
       <div class="button_position">
         <input type="submit" name="create_question" value="<?= __("Submit") ?>" />
       </div>
     </fieldset>
  </div>
</form>
