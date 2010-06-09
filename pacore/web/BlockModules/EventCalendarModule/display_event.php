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
<h2><?= __("Event") ?>: <? echo _out($ed['event_title']); ?></h2>
      <div class="field_event" style="height:90px;">
        <b><?=__("Event Banner")?></b><br />
<?php if (!empty($ed['banner'])) { ?>
          <?php echo uihelper_resize_mk_img($ed['banner'], 430, 80,
          NULL, 'alt="Current Event Banner"', RESIZE_FIT); ?>
<? } ?>
      </div>
    <div class="field_event">
    <b><?= __("Starts") ?></b>:
    <?=PA::datetime($ed['start_time'])?>
    </div>
    <div class="field_event">
    <b><?= __("Ends") ?></b>: <?=PA::datetime($ed['end_time'])?>
    </div>

    
    <div class="field_event">
    <b><?= __("Venue") ?>:</b>:<br />
    <? echo _out($ed['event_venue']); ?>
    </div>
    <div class="field_event">
    <b><?= __("Description") ?>:</b>:<br />
    <? echo _out($ed['event_description']); ?>
    </div>
<? if ($may_edit) { ?>
<div class="button_position">

    <input type="submit" name="edit" value="<?= __("Edit") ?>">


<?php
  if(isset($_REQUEST['gid'])) {
     $on_click = "location.href=location.href.replace(location.search,'?gid=".$_REQUEST['gid']."')";
  } else {
     $on_click = "location.href=location.href.replace(location.search,'')";
  }
?>
    <input type="submit" name="delete" value="Delete Event" onclick="return confirm('<?= __("Are you sure you want to delete this Event?") ?>');<?=$on_click?>"/>

    <input type="button" name="new" value="<?=__("Create new Event")?>" onclick="<?=$on_click?>">
      <input type="button" name="back" value="Cancel" onclick="javascript: history.back();" />
    <input type="hidden" name="edit_event" value="<?= $ed['event_id'] ?>">

</div>
<? } ?>
    
    
                    
