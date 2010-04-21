<?php
global $app;
$current_url = PA::$url . $app->current_route . $app->current_query;
$current_url = preg_replace("#\&template\=[^\&]+#", '', $current_url);
$change_template_JS = "javascript: location.href=\"". $current_url ."&template=\"+$(this).val();";

require_once "web/includes/classes/xHtml.class.php";
  if(!empty($_GET['email_type']) && ($template <> 'text_only')) {
    echo $tiny_mce->installTinyMCE();
  }
  if (!empty($_GET['email_type'])) {
    $class="class=\"display_true\"";
    $style = null;
  } else {
    $class="class=\"display_false\"";
    $style = "style=\"height:120px\"";
  }
?>
<div class="description"><?= __("Configure Email") ?></div>
<form name="config_email_form" action="" method="post">
  <fieldset class="center_box" <?=$style?> >
    <div class="field_medium">
    <h4><label for="select_a_group"><?= __("Select An email type") ?></label></h4>
    <select id="email_type" name="email_type" onchange="javascript: show_email_details('<?php echo PA::$url;?>');" >
        <option value="0"><?= __("Select an email type") ?></option>
      <?php foreach ($email_list as $list) {
        if (@$_GET['email_type'] == $list->type) {
          $selected = "selected=\"selected\"";
        } else {
          $selected = NULL;
        }
      ?>
        <option value="<?php echo $list->type;?>" <?php echo $selected;?>><?php echo $list->description;?></option>
      <?php }?>
    </select>
    </div>
    <div id="email_data" <?php echo $class;?>>
    <input type="hidden" id="category" name="category" value="<?php echo $category;?>" />
    <div class="field">
    <h4><label for="subject"><?= __("Subject") ?></label></h4>
    <input type="text" class="text longer" id="subject" name="subject" value="<?php echo $subject;?>" />
    </div>
    <div class="field">
    <h4><label for="description"><?= __("Description") ?></label></h4>
    <input type="text" class="text longer" id="description" name="description" value="<?php echo $description;?>" />
    </div>
    <div class="field">
    <h4><label for="template"><?= __("Email Template") ?></label></h4>
    <?= xHtml::selectTag($template_list, array('id' => 'template', 'name' => 'template', 'onchange' => $change_template_JS), $template) ?>
    </div>
    <div class="field_bigger">
    <h4><label for="message"><?= __("Message") ?></label></h4>
    <textarea id="email_message" name="email_message" style="width: 500px; height: 520px"><?php echo $message;?></textarea>
    </div>

    <?php
    if (is_array($configurable_variables)) {?>
      <div class="field"><b><?= __("Configurable variables related to the selected message") ?></b>
      <div><ul>
        <?php $configurable_variables_tmp = array_flip($configurable_variables);
        $config_vars = implode(', ', $configurable_variables_tmp);
        echo $config_vars;
        ?>
      </ul></div>
      </div>
    <?php } ?>

    <div class="field">
    <b><?= __("Recipient/Requester configurable variables available in each message") ?></b>
    <div>
      <?= implode(", ", EmailMessages::$recipient_requester_template_vars) ?>
    </div>
    </div>

    <div class="field">
    <b><?= __("Network configurable variables available in each message") ?></b>
    <div>
      <?= implode(", ", EmailMessages::$network_template_vars) ?>
    </div>
    </div>

    <div class="field">
    <b><?= __("Group configurable variables available in each message of Group type") ?></b>
    <div>
      <?= implode(", ", EmailMessages::$group_template_vars) ?>
    </div>
    </div>

    <div class="button_position">
      <input type="hidden" name="config_action" id="config_action_1" value="save_email" />
<!--
      <input type="submit" name="save_email" value="<?= __("Save") ?>" />
      <input type="submit" name="preview_email" value="<?= __("Preview") ?>" />
      <input type="submit" name="restore_default" value="<?= __('Restore Default') ?>" />
-->
    <input type="submit" name="submit" value="<?= __("Save") ?>"/>
    <input name="submit" type="submit" value="<?= __("Preview") ?>" onclick="javascript: document.config_email_form.action ='#preview'; document.getElementById('config_action_1').value='preview_email';"/>
    <input name="submit" type="submit" value="<?= __("Restore Default") ?>" onclick="javascript: document.getElementById('config_action_1').value='restore_defaults';"/>
<!--
    <?php if(PA::$login_uid == SUPER_USER_ID) : ?>
      <input name="submit" type="submit" value="<?= __("Store as Defaults") ?>" onclick="javascript: document.getElementById('config_action_1').value='store_as_defaults';" />
      <div style="float:right; margin-right: 74px; color: red"><?= __("Caution - software developers only") ?></div>
    <?php endif; ?>
-->
    </div>
    </div>
  </fieldset>
</form>
 <?php  if(empty($_GET['email_type'])) : ?>
 <div>
  <form method="post" action="" enctype="multipart/form-data">
  <fieldset class="center_box" style="height:126px;">
   <div class="field_biger" style="float:left; width:50%; border-right:1px solid silver">
    <div style="float:left;">
     <h4><label for="slogan-sub"><?= __("Load / restore Email Message settings from local disk") ?></label></h4>
     <input name="local_file" id="local_file" type="file" value="" />
    </div>
    <div class="field_text"  style="width:50%">
      <p><?= __('Select XML configuration file.') ?></p>
    </div>
   </div>
   <div class="field_biger" style="float:left; margin-left:12px;">
     <h4><label><?= __("Select action") . ": " ?></label></h4>
     <input type="hidden" name="config_action" id="config_action_2" value="load_email_messages" />
     <input name="submit" type="submit" value="<?= __("Load Email Message settings") ?>" /><br />
     <input name="submit" type="submit" value="<?= __("Revert All Messages") ?>" onclick="javascript: if(confirm('Are you sure?')) { document.getElementById('config_action_2').value='revert_all_messages'; return true;} else {return false;}"/>
   </div>
      <div style="float:left; width: 220px; height: 24px; margin-left: 14px; color: red"><?= __("Warning: you will lose all message changes made at this site.") ?></div>
  </fieldset>
  </form>
 </div>
 <?php  endif; ?>
<br />
<br />
<?php if(!empty($preview)) : ?>
<div>
  <h1><a name="preview"><?= __("Preview") ?></a></h1>
  <?php echo $preview;?>
</div>
<?php endif; ?>