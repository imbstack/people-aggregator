<?php
require_once "api/Permissions/PermissionsHandler.class.php";
?>
<div class="total_content">
<ul id="filters">
  <li <?php echo (!isset ($_GET['type']) || ($_GET['type'] == 'Images')) ?
  'class = "active"':''; ?> id="epm0"><a
  href="view_all_media.php?type=Images&celebrity_id=<?php echo
  $item_id;?>">Image</a></li>
 
  <li <?php echo (!isset ($_GET['type']) || ($_GET['type'] == 'Videos')) ?
  'class = "active"':''; ?> id="epm0"><a
  href="view_all_media.php?type=Videos&celebrity_id=<?php echo
  $item_id;?>"><?= __("Video") ?></a></li>
</ul>
</div>
<div id="buttonbar">
  <ul> 
    <?php if (!empty(PA::$login_uid)) { ?><li><a href="<?php echo PA::$url;?>/upload_celebrity_media.php?item_id=<?php echo $item_id;?>&type=image"><?= __("Upload") ?></a></li><?}?>
  </ul>
</div> 

<div class="media_gallery_thumb" id="image_gallery_thumb">
<br />
<?php if (!empty($links)) {
  $cnt = count($links);
?>
<ul>
<?php foreach ($links as $image) {?> 
             <?php $params = array( 'permissions'=>'edit_content', 'uid'=>PA::$login_uid, 'cid'=>$image->content_id );?>
          <li id="image_<?php echo $image->content_id;?>">
            <a href="<?php echo PA::$url;?>/media_full_view.php?cid=<?php echo $image->content_id;?>"><?php echo  uihelper_resize_mk_img($image->file_name, 70, 50);?></a>
            <p class="name">
              <a href="<?php echo PA::$url;?>/media_full_view.php?cid=<?php echo $image->content_id;?>"> <?php echo chop_string($image->title, 8);?></a>
            </p>
            <?php if(PermissionsHandler::can_user(PA::$login_uid, $params)) {?>
              <p class="choose">
                <a href="<?php echo PA::$url;?>/upload_celebrity_media.php?item_id=<?php echo $item_id;?>&type=image&do=edit&cid=<?php echo $image->content_id;?>"><img src="<?php echo PA::$theme_url;?>/images/16_edit.gif" alt="" height="16" width="16" border="0" title="Edit" /></a>
                <a href="<?php echo PA::$url . PA_ROUTE_CONTENT . "?action=deleteContent&cid=$image->content_id&back_page=" . urlencode(PA::$url . "/view_all_media.php?celebrity_id=$item_id") ?>"><img src="<?php echo PA::$theme_url;?>/images/16_delete.gif" alt="" height="16" width="16" border="0"  title="Delete" onclick="return confirm_delete('<?= __("Are you sure you want to delete this image") ?>');"/></a>
              </p>
            <?php }?>
          </li>
<?} // End of for loop?>  
</ul>
<?} else { ?>
  <ul>
    <li>
      <?= __("No Photos.") ?>
    </li>
  </ul>  
<? } ?>
<?php if ($page_links) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>
</div>