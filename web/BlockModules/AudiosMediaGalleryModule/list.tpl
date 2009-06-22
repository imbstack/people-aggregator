<?php 
  $temp = $links;
 
  unset($temp['album_id']);
  unset($temp['album_name']);
  // Handling Group Media gallery
  $gid = (!empty($_GET['gid']))?'&gid='.$_GET['gid']:NULL;
  $uid = (!empty($_GET['uid']))? $_GET['uid']:NULL;
?>
<div class="media_gallery_list" id="image_gallery_list">
<table cellpadding="2" cellspacing="0" class="fleft">
  <?php if (!empty($temp)) { ?>
  <tr>
    <th width="40">
      &nbsp;
    </th>
    <th width="76">
      <?= __("Thumbnail") ?>
    </th>
    <th width="736">
      <?= __("Title/Description/Date/Tags") ?>
    </th>
    <th width="100">
      <?= __("Album/Playlist") ?>
    </th>

  </tr>
  <?php for ( $i = 0; $i < count( $links )-2; $i++) {
          /* code for Image path */
          if($links[$i]['type'] == 7) {
                $image_hyperlink = PA::$url . PA_ROUTE_CONTENT . "/cid=".$links[$i]['content_id'];
                $link_image = "$current_theme_path/images/sb-70X50.jpg";
            } else {
                $image_hyperlink = PA::$url . "/media_full_view.php?cid=".$links[$i]['content_id']."&amp;type=audio";
                if(isset($gid)) $image_hyperlink .= $gid;  // if Group Media gallery

                $link_image = "$current_theme_path/images/audio-icon.gif";
            }
          /* Links for editing content */
           if($links[$i]['type'] == 7) {
                $link_for_editing = PA::$url . "/post_content.php?cid=".$links[$i]['content_id']
                ;
            } else {
                $link_for_editing = PA::$url . "/edit_media.php?uid=".$uid."&amp;cid=".$links[$i]['content_id'];
            }

  ?>
  
  <tr>
    <td>
    <?php if ($uid == PA::$login_uid && empty($_GET['gid'])) { ?>
    
      <a href="<?php echo $link_for_editing;?>"><img src="<?php echo $current_theme_path;?>/images/16_edit.gif" alt="edit" height="16" width="16" border="0" title="Edit"></a>
      <a href="#"><img src="<?php echo $current_theme_path;?>/images/16_delete.gif" alt="delete" height="16" width="16" border="0" title="Delete" onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload1');"></a>
     <? } if (isset($_GET['gid']) && !empty ($_GET['gid']) && ($links[$i]['author_id'] == PA::$login_uid )) {// Handling Group Media Gallery
       
       if ($links[$i]['type'] == 7) {
         $link_for_editing = PA::$url . "/post_content.php?cid=".$links[$i]['content_id'];
       } else {
         $link_for_editing = PA::$url . "/edit_media.php?uid=".PA::$login_uid."&amp;cid=".$links[$i]['content_id']."&amp;type=Audios";
       }
    ?>

      <a href="<?php echo $link_for_editing;?>"><img src="<?php echo $current_theme_path;?>/images/16_edit.gif" alt="edit" height="16" width="16" border="0"></a>
      <a href="#"><img src="<?php echo $current_theme_path;?>/images/16_delete.gif" alt="delete" height="16" width="16" border="0"  onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload1');"></a>

    <?}?>
    </td>
    <td>
      <div align="center">
        <a href="<?php echo $image_hyperlink; ?>">
          <img src="<?php echo $link_image; ?>" alt="PA" border="0" width="35" height="30" border="0" class="img_list" />
      </div>

   </td>
   <td>
      <ul>
        <li><h1><a href="<?php echo $image_hyperlink; ?>">
            <? echo _out($links[$i]['title']); ?>
            </a>
            </h1>
        </li>
        <?php
          if (!empty($links[$i]['body'])) {
        ?>
        <li><?=$links[$i]['body']?></li>
        <?php
          }
        ?>
        <li>
          <div class="post_info">
            <b>Created:</b> <?=content_date($links[$i]['created']);?> 
            <b><?php if (!empty($links[$i]['tags'])) { ?> 
             Tags:<?=$links[$i]['tags'];?>
            <? } ?></b>
           
          </div>
        </li>
      </ul>
    </td>
    <td>
      <?php
        if($gid) {
          $album_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/view=groups_media" . $gid;
        } else {
          $album_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_AUDIOS . "/uid=" . $uid;
        }
      ?>
      <a href="<?= $album_url ?>">
         <?php print stripslashes($links['album_name']);?>
      </a>
   </td>
  </tr>
  <? } ?>
  <? } else { ?>
   <tr>
     <td>
        <?= __("No Audio") ?>
     </td>
   </tr>
  <? } ?>
</table>  
</div>