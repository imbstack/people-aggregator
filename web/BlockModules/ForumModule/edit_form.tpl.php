<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
 
if(is_object($tiny_mce)) {
  echo $tiny_mce->installTinyMCE();
}

?>

  <div class="forums">
    
    <table class="forum_main" align="center">
    <thead>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td class="left_top"></td>
        <td class="top_navigation">&nbsp;</td>
        <td class="right_top"></td>
      </tr>
      <tr>
        <td></td>
        <td colspan="1" class="spacer">
          <?php include("forum_header.tpl.php"); ?>
        </td>
        <td></td>
      </tr>
      </table>
    </tr>
    </thead>
    
    <tbody>
    <tr>
      <td colspan="4">
        <table class="board">
        <thead>
          <tr>
            <th class="board_left_top"></th>
            <th class="board_mid_top"></th>
            <th class="board_right_top"></th>
          </tr>
        </thead> 
        <tbody>
          <tr> 
            <td class="board_left_mid"></td>
            <td>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td>
                  <table class="board_inner" align="center">
                  <thead>
                    <tr>
                      <th class="thead align_center" width="100%">
                        <?php echo "<b>".__($title) ."</b> " ?>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td class="alt1">
                        <div class="edit_reply">
<!--                        
                          <form name="edit_form" action="<?= "$forums_url/action=". $action ?>" method="POST" id="edit_form" class="reply_form">
-->                          
                          <form name="edit_form" action="<?= $forums_url ?>" method="POST" id="edit_form" class="reply_form">
                            <?php foreach($fields['fields'] as $field) : ?>
                            <div class="<?= $field['class'] ?>">
                              <label for="form_data_<?= $field['name'] ?>">
                                <?php if($field['required']) : ?>
                                  <span class="required">*</span>
                                <?php endif; ?>  
                                <?= __($field['label']) ?>:
                              </label>
                              <?php if($field['type'] == 'text') : ?>
                                <input type="text" name="form_data[<?= $field['name'] ?>]" id="form_data_<?= $field['name'] ?>" value="<?= (!empty($field['content'])) ? $field['content'] : null ?>" />
                              <?php elseif($field['type'] == 'textarea') : ?>
                                <textarea name="form_data[<?= $field['name'] ?>]" id="form_data_<?= $field['name'] ?>" >
                                  <?= (!empty($field['content'])) ? $field['content'] : null ?>
                                </textarea>
                              <?php elseif($field['type'] == 'checkbox') : ?>
                                <input type="checkbox" name="form_data[<?= $field['name'] ?>]" id="form_data_<?= $field['name'] ?>" <?= (($field['content']) == 'checked') ? "checked" : null ?> />
                              <?php elseif($field['type'] == 'select') : $selected = (isset($field['content']['selected'])) ? $field['content']['selected'] : null ?>
                                <select name="form_data[<?= $field['name'] ?>]" id="form_data_<?= $field['name'] ?>" >
                                <?php foreach($field['content']['options'] as $value => $name) : ?>
                                  <option value="<?=$value ?>" <?=(($value == $selected) ? "selected=\"selected\"" : null)?> ><?=$name?></option>
                                <?php endforeach; ?>
                                </select>  
                              <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <?php foreach($fields['hidden_fields'] as $h_field_name => $h_field_value) : ?>
                              <input name="<?= $h_field_name ?>" id="<?= $h_field_name ?>" type="hidden" value="<?= (!empty($h_field_value)) ? $h_field_value : null ?>" />
                            <?php endforeach; ?>
                            <input name="action" id="action" type="hidden" value="<?= $action ?>" />
                            <div class="buttons_panel">
                            <a href="<?= $back_url ?>">
                              <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/cancel.gif" ?>" alt="cancel" class="forum_buttons"/>
                            </a>&nbsp;
                            <a href="#" onclick="javascript: document.forms['edit_form'].submit(); return false;">
                              <img src="<?php echo $theme_url . "/images/buttons/" . PA::$language . "/send.gif" ?>" alt="send" class="forum_buttons"/>
                            </a>
                            </div>
                          </form>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                  </table>
                </td>
              </tr>
              </table>
            </td>
            <td class="board_right_mid"></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th class="board_left_bottom"></th>
            <th class="board_mid_bottom"></th>
            <th class="board_right_bottom"></th>
          </tr>
        </tfoot>
        </table>
      </td>
    </tr>  
  </tbody>

  <tfoot>
    <tr>
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td></td>
        <td colspan="1" class="spacer"></td>
        <td></td>
      </tr>
      <tr>
        <td class="left_bottom"></td>
        <td class="bottom_navigation">&nbsp;</td>
        <td class="right_bottom"></td>
      </tr>
      </table>
    </tr>
  </tfoot>
  
 </table>
</div> 