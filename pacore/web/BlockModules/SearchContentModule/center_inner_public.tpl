<?php
?>

<div class="description"></div>
<form name="searchForm" method="get" action="" onsubmit="javascript: return validateDate(document.searchForm);">

  <fieldset class="center_box">
    <legend><?= __("Search Terms") ?></legend>

      <div class="field">
        <h4><label><?= __("all of these words") ?></label></h4>
        <input type="text" name="form_data[allwords]" value="<?php echo htmlspecialchars(@$form_data['allwords']) ?>" class="text longer" />
      </div>
      <div class="field">
        <h4><label><?= __("this exact phrase") ?></label></h4>
        <input  type="text" name="form_data[phrase]" value="<?php echo htmlspecialchars(@$form_data['phrase']) ?>" class="text longer" />
      </div>
      <div class="field">
        <h4><label><?= __("any of these words") ?></label></h4>
        <input  type="text" name="form_data[anywords]" value="<?php echo htmlspecialchars(@$form_data['anywords']) ?>" class="text longer" />
      </div>
      <div class="field">
        <h4><label><?= __("none of these words") ?></label></h4>
        <input  type="text" name="form_data[notwords]" value="<?php echo htmlspecialchars(@$form_data['notwords']) ?>" class="text longer" />
      </div>
<br/>
   <h3><?= __("Date Range") ?></h3>
     <div class="field">
       <h4><label><?= __("Start Date") ?></label></h4>
       <select name="form_data[mFrom]">
            <option value="0" selected="selected"> - <?= __("Select") ?> </option>
            <?php echo month_options(@$form_data['mFrom']);?>
       </select>
       <select name="form_data[dFrom]">
         <option value="0" selected="selected"> - <?= __("Select") ?> </option>
         <?php echo date_options(@$form_data['dFrom']);?>
       </select>
       <select name="form_data[yFrom]">
         <option value="0" selected="selected"> - <?= __("Select") ?> </option>
         <?php echo year_options(@$form_data['yFrom'])?>
       </select>
     </div>

     <div class="field">
       <h4><label><?= __("End Date") ?></label></h4>
       <select name="form_data[mTo]">
         <option value="0" selected="selected"> - <?= __("Select") ?> </option>
         <?php echo month_options(@$form_data['mTo']);?>
       </select>
       <select name="form_data[dTo]">
         <option value="0" selected="selected"> - <?= __("Select") ?> </option>
         <?php echo date_options(@$form_data['dTo']);?>
       </select>
       <select name="form_data[yTo]">
         <option value="0" selected="selected"> - <?= __("Select") ?> </option>
         <?php echo year_options(@$form_data['yTo'])?>
       </select>
     </div>
  </fieldset>
  <div class="button_position">
    <input type="hidden" name="action" value="submitSearch" />
    <input type="submit" name="btn_searchContent" value="<?= __("Search Content") ?>" />
  </div>

</form>
