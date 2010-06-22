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
if(!empty($families[0])) {
    if(0) {
        // Testcode for on_same_family
        if(PA::$login_uid != PA::$page_uid) {
            require_once("api/Entity/FamilyTypedGroupEntity.php");
            echo "<pre>".print_r(FamilyTypedGroupEntity::in_same_family(PA::$login_uid, PA::$page_uid), 1)."</pre>";
        }
    }
    // end test code
    ?>
<div class="module_icon_list">
  <ul class="members">
  <?php foreach($families as $family) {?>
    <li>
      <a href="<?=PA::$url.PA_ROUTE_FAMILY."/gid=".$family['id']?>">
        <?php echo uihelper_resize_mk_img($family['picture'], 35, 35, "images/default_group.gif", 'alt="group image"', RESIZE_CROP)?>
      </a>
      <span>
        <b>
          <a href="<?=PA::$url.PA_ROUTE_FAMILY."/gid=".$family['id']?>">
            <?php echo chop_string(stripslashes($family['title']), 17);?>
          </a>
       </b><br/>
       <?php echo uihelper_plural($family['members'], ''.__('member').'')?>
     </span>
   </li>
   <?php
    }
    // End of For?>
  </ul>
</div>
<?
}
else {
    //End of If condition?>
<div class="module_browse_groups">
  <ul>
    <?if($mode == 'private' || !empty($user_name)) {?>
      <li><?=sprintf(__('You haven\'t <a href="%s">created</a> or <a href="%s">joined</a> any families yet.'), PA::$url.PA_ROUTE_FAMILY_EDIT, PA::$url.PA_ROUTE_FAMILY_DIRECTORY)?>
      </li>
      <?php
    }
    else {?>
      <li><span><?=__('User has not joined any family yet.');?></span></li>
    <?php
    }?>
  </ul>
</div>
<?
}?>
