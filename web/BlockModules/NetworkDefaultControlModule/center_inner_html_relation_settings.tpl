<?php
  
  // added by: Z.Hron 
  // implements feature #0011955: 
  // Relationship Settings - add 'One Kind of Friend' admin option
  $smode_checked = 1;    // default values
  $term = __('Friend');
  if(isset($form_data['extra']['relationship_show_mode'])) {
    $sm_data = $form_data['extra']['relationship_show_mode'];
    $smode_checked = $sm_data['value'];
    $term = (isset($sm_data['term'])) ? $sm_data['term'] : '';
  }
  $js1 = "javascript:document.getElementById('show_mode_2').style.display = 'none';
                     document.getElementById('show_mode_1').style.display = 'block';";
  $js2 = "javascript:document.getElementById('show_mode_1').style.display = 'none';
                     document.getElementById('show_mode_2').style.display = 'block';";

?>

<h1>Relationship Settings</h1>
  <form method="post" action="" >
    <fieldset class="center_box">
     <div class="field_big">
        <h4><b><label for="relationship_show_mode">Relationship Settings:</label></b></h4>
        <div>
           <input name="relationship_show_mode" value="1" type="radio" <?=($smode_checked==1) ? 'checked="checked"': ''?> onclick="<?=$js1?>"> one kind of relation, such as 'Friend'<br />
           <input name="relationship_show_mode" value="2" type="radio" <?=($smode_checked==2) ? 'checked="checked"': ''?> onclick="<?=$js2?>"> enable 5 levels of relations/friends
        </div>
     </div>
       <div id="show_mode_1" class="field_big" style="display:<?=($smode_checked==1) ? 'block' : 'none'?>">
        <p>Choose the term that will be used for relations in your network.</p>
        <label>Current: </label><input name="relationship_term" type="text" class="text" style="float:none" value="<?=htmlspecialchars($term)?>" />
      </div>
     <div id="show_mode_2" style="display:<?=($smode_checked==2) ? 'block' : 'none'?>">
       <p>Choose the names of the five varying degrees of relations in your network.
          Relation names are ordered closest to most distant in the list below. The
          default values are provided as a starting point. </p>
      
       <?php foreach ( $form_data['extra']['relationship_options'] as $key => $value) :
           echo "<div class=\"field\" ><input name=\"$key\" type=\"text\" class=\"text\" value=\"".htmlspecialchars($value['value'])."\" /></h4><label>
           <h4>".$value['caption']."</h4> </label> </div>";
       endforeach;?>
     </div>
  <div class="button_position">    
    <input type="hidden" name="config_action" id="config_action_1" value="save" />
    <input type="submit" name="submit" value="<?= __("Save Relationship") ?>"/>
    <input name="submit" type="submit" value="<?= __("Restore Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='restore_defaults';"/>
    <input name="submit" type="submit" value="<?= __("Store as Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='store_as_defaults';" />
  </div> 
     </fieldset>
  </form>
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box">  
   <div class="field_biger" style="float:left; width:50%; border-right:1px solid silver">
    <div style="float:left;">
     <h4><label for="slogan-sub"><?= __("Load / restore User Account Defaults from local disk") ?></label></h4>
     <input name="local_file" id="local_file" type="file" value="" />
    </div>
    <div class="field_text"  style="width:50%">
      <p><?= __('Select XML configuration file.') ?></p>
    </div>
   </div>
   <div class="field_biger" style="float:left; margin-left:12px">
     <h4><label><?= __("Select action") . ": " ?></label></h4>
     <input type="hidden" name="config_action" id="config_action_2" value="load_general_settings" />
     <input name="submit" type="submit" value="<?= __("Load User Account Defaults") ?>" /><br />
   </div>
  </fieldset>
  </form>
     <?php echo $config_navigation_url; ?>
