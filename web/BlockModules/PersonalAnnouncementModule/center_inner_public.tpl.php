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
<?php if($mod->edit_permission) : ?>
  <div id="user_announcement" class="inplace_edit"  style="margin: 4px" ajaxUrl="<?= PA_ROUTE_USER_PUBLIC . '/' . PA::$page_uid . "&module=PersonalAnnouncementModule&action=updateUserAnnouncement" ?>" minHeight="64px" tinyMCE="false">
    <?=_out($mod->announcement)?>
  </div>
<?php else : ?>
  <p style="margin: 4px">
    <?=_out($mod->announcement)?>
  </p> 
<?php endif; ?>
 
