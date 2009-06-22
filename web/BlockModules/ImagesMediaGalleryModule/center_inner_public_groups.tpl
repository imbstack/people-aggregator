<?php global $app, $current_theme_path;
  $current_url = PA::$url . $app->current_route . "/view=groups_media";
?>

<?php if (!empty ($my_all_groups)) {?> 
<div class="search_gallery"><?if (!empty($my_all_groups)) { echo 'Groups'; } ?>
  <select id="group_list" class="select-txt" onchange="select_group()">
    <?php for ($k=0; $k<count($my_all_groups); $k++) { ?>
      <?php if ($my_all_groups[$k]['gid'] == $_GET['gid']) {
            $selected = "selected=\"selected\""; 
          }
          else {
            $selected = " ";
          }
        ?>
       <option <?=$selected?> value="<?=$my_all_groups[$k]['gid']?>"><?=$my_all_groups[$k]['name']?></option>
     <?php } ?>
  </select>
</div>
<?}?>
<?php $display_links = ($show_view == 'thumb') ? 'List View': 'Thumb View';?>
<?php $href_links = ($show_view == 'thumb') ? '&gallery=list&gid=' . $_GET['gid']: '&gallery=thumb&gid=' . $_GET['gid'];?>
<div id="buttonbar">
  <ul> 
    <li><a href="<?= $current_url . $href_links;?>"><?php echo $display_links;?></a></li>

    <li><a href="<?= PA::$url . '/upload_media.php?type=Images&amp;gid=' . $_GET['gid'] ?>"><?= __("Upload") ?></a></li>
  </ul>
</div> 
<form enctype="multipart/form-data" id="image_upload" name="image_upload" action="" method="POST">
  <input type="hidden" name="action" value="deleteMedia" />
  <input type="hidden" name="media_id" id="media_id" value="" />
  <?php
   switch ($show_view) {
     case 'thumb':
       require "thumbnail.tpl";
     break;
     case 'list' :
       require "list.tpl";
     break;
   }
    ?>
</form>