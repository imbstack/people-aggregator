<?php
// include "_menu.tpl";
if(!empty($_GET['to_file'])) {
  $fname = "/opt/lampp/htdocs/dev3/pacore/web/config/ModulesInfo.txt";
  $fh = fopen($fname, 'a+') or die("can't open file");
  echo "Module data will be written to \"$fname\" file";
}
?>
<div style="float:left">

<form name="create_page" action="/tools/CreateDynamicPage/?action=CreatePageSubmit" method="POST">
<fieldset class="center_box">
  <div class="field_big" style="padding: 4px;">
    <h4><label for="form_data_page_id"><?= __('Select page') . ': ' ?></label></h4>
    <div class="center" style="width: 260px;"><?= $select_tag ?></div>
    <div class="field_text"><br>
      <?= __('Select page you want to customize.') ?>
    </div>
  </div>
  <div class="field_bigger" style="padding: 4px;">
    <h4><label for="form_data_page_id"><?= __('Get module info') . ': ' ?></label></h4>
    <div class="center" style="width: 260px;"><?= $mod_select_tag ?></div>
    <div class="field_text"><br>
      <?php if($module_info) : $positions = explode("|", $module_info['module_placement']) ?>
        <?= __('Add this module to: ') ?>
        <ul>
          <?php foreach($positions as $position) : ?>
            <li style="float:left; border:none"><a href="<?= PA_ROUTE_CREATE_DYN_PAGE ."?action=edit&id=".$_REQUEST['id']."&module=" .$_REQUEST['module']."&add=$position" ?>"><?= $position ?></a></li>
          <?php endforeach; ?>
        <ul>
      <?php endif; ?>
    </div>
  </div>
    <?php if($module_info) : ?>

<?php if(!empty($_GET['to_file'])) : ?>
<?php
   fwrite($fh, "Module name: " . $module_info['name'] . "\n");
   fwrite($fh, "----------------------------------------------------------------------------------\n");
   fwrite($fh, "Module type: " . $module_info['module_type'] . "\n");
   fwrite($fh, "Placement/Layout: " . $module_info['module_placement'] . "\n");
   fwrite($fh, "Estimated level of completion: " . $module_info['status_points'] . "%\n\n");
   fclose($fh);
?>
<?php endif; ?>

    <div class="field_bigger" style="padding: 4px;">
      <ul style="width:100%">
        <li><span style="float:left; width:220px; font-weight:bold"><?= "Module name: " ?></span><?= $module_info['name'] ?></li>
        <li><span style="float:left; width:220px; font-weight:bold"><?= "Module type: " ?></span><?= $module_info['module_type'] ?></li>
        <li><span style="float:left; width:220px; font-weight:bold"><?= "Placement : " ?></span><?= $module_info['module_placement'] ?></li>
        <li><span style="float:left; width:220px; font-weight:bold"><?= "Probably refactored : " ?></span><?= $module_info['status_points'] ?>%</li>
      </ul>
    </div>
    <?php endif; ?>
  <div class="field_bigger" style="padding:4px">
    <label for="form_data_page_id"><span class="required"> * </span><b> <?= __("Page ID") ?>:</b></label>
    <input type="text" name="form_data[page_id]" class="text long" id="form_data_page_id" style="width:50px;" value="<?=$page->page_id?>"/>
    <div class="field_text"><br />
      <?= __("If you want to create a new page, enter here an unique Page ID number.") ?><br />
      <b><?= __("For dynamic pages, this ID number should start from 200.") ?></b>
    </div>
  </div>
  <div class="field_big" style="height:64px; padding:4px">
    <label for="form_data_page_name"><span class="required"> * </span><b> <?= __("Page Title") ?>:</b></label>
    <input type="text" name="form_data[page_name]" class="text long" id="form_data_page_name" style="width:350px;" value="<?=$page->page_name?>"/>
    <div class="field_text">
       <br /><?= __("Enter Page name") ?><br/>
    </div>
  </div>
  <div class="field_big" style="height:84px; padding:4px">
    <label><b> <?= __("Login required") ?>:</b></label>
    <div class="center">
      <label for="form_data_page_mode_1"><?= __("No") ?></label>
      <input type="radio" name="form_data[page_mode]" id="form_data_page_mode_1" value="public" <?=($page->page_mode == 'public') ? 'checked="checked"' : null?> />
      <br />
      <label for="form_data_page_mode_2"><?= __("Yes") ?></label>
      <input type="radio" name="form_data[page_mode]" id="form_data_page_mode_2" value="private" <?=($page->page_mode == 'private') ? 'checked="checked"' : null?> />
    </div>
    <div class="field_text">
       <br /><?= __("Select Page type") ?><br />
    </div>
  </div>
  <div class="field_big" style="height:64px; padding:4px">
    <label for="form_data_page_type"><span class="required"> * </span><b> <?= __("Page type") ?>:</b></label>
    <input type="text" name="form_data[page_type]" class="text long" id="form_data_page_type" style="width:350px;" value="<?=$page->page_type?>"/>
    <div class="field_text">
       <br /><?= __("Enter Page type (user, group, network). For multiple types use \"|\" to separate types.") ?><br/>
    </div>
  </div>
  <div class="field_big" style="height:84px; padding:4px">
    <label><b> <?= __("Is page configurable") ?>:</b></label>
    <div class="center">
      <label for="form_data_is_configurable_1"><?= __("No") ?></label>
      <input type="radio" name="form_data[is_configurable]" id="form_data_is_configurable_1" value="0" <?=($page->is_configurable == false) ? 'checked="checked"' : null?> />
      <br />
      <label for="form_data_is_configurable_2"><?= __("Yes") ?></label>
      <input type="radio" name="form_data[is_configurable]" id="form_data_is_configurable_2" value="1" <?=($page->is_configurable == true) ? 'checked="checked"' : null?> />
    </div>
    <div class="field_text">
       <br /><?= __("If a page is configurable - these settings will be available in Customize UI pages.") ?><br />
    </div>
  </div>
  <div class="field_bigger" style="height:120px; padding:4px">
    <label for="form_data_access_permission"><?= __("Required user or administration permissions for access to this page") ?>: </label>
    <input type="text" name="form_data[access_permission]" class="text long" id="form_data_access_permission" style="width:350px;" value="<?=$page->access_permission?>"/>
    <div class="field_text">
       <br /><?= __("Enter required administration permissions. Currently, these permissions are available") ?>: <br /><br />
       <b><?= $adm_permissions ?></b>
    </div>
  </div>
  <div class="field_big" style="height:64px; padding:4px">
    <label for="form_data_page_theme"><?= __("Page Theme") ?>: </label>
    <input type="text" name="form_data[page_theme]" class="text long" id="form_data_page_theme" style="width:350px;" value="<?=$page->page_theme?>"/>
    <div class="field_text">
       <br /><?= __("Enter Page theme (Beta)") ?><br />
    </div>
  </div>
  <div class="field_big" style="height:64px; padding:4px">
    <label for="form_data_page_template"><?= __("Page Outer Template") ?>: </label>
    <input type="text" name="form_data[page_template]" class="text long" id="form_data_page_template" style="width:350px;" value="<?=$page->page_template?>"/>
    <div class="field_text">
       <br /><?= __("Enter Page Outer Template (container_three_column.tpl, container_two_column.tpl ...)") ?><br />
    </div>
  </div>
  <div class="field_big" style="height:64px; padding:4px">
    <label for="form_data_header_template"><?= __("Header Template") ?>: </label>
    <input type="text" name="form_data[header_template]" class="text long" id="form_data_header_template" style="width:350px;" value="<?=$page->header_template?>"/>
    <div class="field_text">
       <br /><?= __("Enter Page Header Template (header.tpl, header_group.tpl ...)") ?><br />
    </div>
  </div>
  <div class="field_bigger" style="height: 358px; padding:4px">
    <label for="form_data_boot_code"><?= __("Page boot (pre-execute) PHP code") ?></label>
    <div>
       <textarea  name="form_data[boot_code]" id="form_data_boot_code" style="height:128px; width: 450px"><?=$page->boot_code?></textarea>
    </div>
    <div class="field_text">
       <br /><?= __("Enter here code that should be executed on page boot before any module initialization function called. Use this only if you have a good reason for that and when you can't place that code inside a module - initializeModule() function. For example, if some modules need and share same data and you want to avoid executing same code multiple times. In that case, place here required code to evaluate those data and forward results to each module via \$module_shared_data variable. For example") ?>: <br />
       <pre>
        $params = array('page'=>1,'show'=>5);
        $users = Network::get_network_members($network_info->network_id,$params);
        $network_id = $network_info->network_id;
        $module_shared_data['users'] = $users;
        $module_shared_data['network_id'] = $network_id;
       </pre>
      <b><?= __("NOTE: If your page does not require additional boot code, leave this untouched.") ?></b>
    </div>
  </div>
  <div class="field_bigger" style="padding:4px">
    <label for="form_data_left"><?= __("Left modules") ?></label>
    <div>
       <textarea name="form_data[left]" id="form_data_left" style="height:64px; width: 350px"><?=(is_array($page->left)) ? implode(',', $page->left) : $page->left?></textarea>
    </div>
    <div class="field_text">
       <?= __("Enter Left modules separated by commas") ?>
    </div>
  </div>
  <div class="field_bigger" style="padding:4px">
    <label for="form_data_middle"><?= __("Middle modules") ?></label>
    <div>
       <textarea name="form_data[middle]" id="form_data_middle" style="height:64px; width: 350px"><?=(is_array($page->middle)) ? implode(',', $page->middle) : $page->middle?></textarea>
    </div>
    <div class="field_text">
       <?= __("Enter Middle modules separated by commas") ?>
    </div>
  </div>
  <div class="field_bigger" style="padding:4px">
    <label for="form_data_right"><?= __("Right modules") ?></label>
    <div>
       <textarea name="form_data[right]" id="form_data_right" style="height:64px; width: 350px"><?=(is_array($page->right)) ? implode(',', $page->right) : $page->right?></textarea>
    </div>
    <div class="field_text">
       <?= __("Enter Right modules separated by commas") ?>
    </div>
  </div>
  <div class="field_bigger" style="padding:4px">
    <label for="form_data_javascripts"><?= __("Page JavaScript files") ?></label>
    <div>
       <textarea  name="form_data[javascripts]" id="form_data_javascripts" style="height:64px; width: 350px"><?=(is_array($page->javascripts)) ? implode(',', $page->javascripts) : $page->javascripts?></textarea>
    </div>
    <div class="field_text">
       <?= __("Enter page Javascript file names separated by commas") ?>
    </div>
  </div>
  <div class="field_bigger" style="padding:4px">
    <label for="form_data_page_css"><?= __("Page CSS files") ?></label>
    <div>
       <textarea  name="form_data[page_css]" id="form_data_page_css" style="height:64px; width: 350px"><?=(is_array($page->page_css)) ? implode(',', $page->page_css) : $page->page_css?></textarea>
    </div>
    <div class="field_text">
       <?= __("Enter page CSS file names separated by commas") ?>
    </div>
  </div>
  <div class="field_bigger" style="height: 348px; padding:4px">
    <label for="form_data_navigation_code"><?= __("Page navigation links PHP code") ?></label>
    <div>
       <textarea  name="form_data[navigation_code]" id="form_data_navigation_code" style="height:128px; width: 450px"><?=$page->navigation_code?></textarea>
    </div>
    <div class="field_text">
       <br /><?= __("Enter code required for generating page navigation links. Here should be moved code from function") ?>
       <b>get_links()</b> <?= __("declared in") ?> <b>Navigation</b> <?= __("class (web/includes/classes/Navigation.php)") ?>
       <?= __("inside") ?> <b>switch ( $this->current_page )</b> <?= __("statement") ?>. <?= __("Please see an example below. In this example we will use code written for generating navigation links on") ?> <b>PA_ROUTE_PEOPLES_PAGE</b> <?= __("page") ?>:<br />
       <pre>
        $level_2['highlight'] = 'people';
        $level_3 = $this->get_level_3('people');
        $level_3['highlight'] = 'find_people';
       </pre>
       <?= __("So, this PHP code block should be copied and pasted in a text field above. If old") ?> <b>FILE_PEOPLES</b> <?= __("page will not be used anymore, this code block should be removed from Navigation class") ?>. (<?= __("See") ?> Navigation.php,
       <?= __("line") ?> 817)<br />
       <b><?= __("NOTE: Code in the box above is only for example. You must delete it or enter your menu- navigation code here!") ?></b>
    </div>
  </div>
  <div class="field_bigger" style="height: 128px; padding:4px">
    <label for="form_data_body_attributes"><?= __("Page body attributes") ?></label>
    <div>
       <textarea  name="form_data[body_attributes]" id="form_data_body_attributes" style="height:64px; width: 350px"><?=$page->body_attributes?></textarea>
    </div>
    <div class="field_text">
      <br /> <?= __("Enter page body CSS attribute. For example") ?>: <b>class='no_second_tier'</b> or <br />
      <b>style='width:600px; height: 800px; font-familly: Tahoma'</b>
    </div>
  </div>
  <div class="field_big" style="height:64px; padding:4px">
    <div class="center">
      <label for="form_data_page_mode_1"><?= __("Save generated page") ?></label>
      <input type="checkbox" name="save_page" id="save_page" value="1" />
    </div>
    <div class="field_text">
       <br />
       <?= __("Select if you want to save generated Page") ?>
    </div>
  </div>
  <br />
  <div class="button_position">
     <input type="submit" name="addPage" value="<?= __("Submit") ?>" />
  </div>
</fieldset>
</form>
</div>