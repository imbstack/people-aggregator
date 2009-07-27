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
  <?php if ( !empty( $temp )) { ?>
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
          /* code for Image path and hyperlink of that image */
          if ( $links[$i]['type'] == 7) {
               $image_hyperlink = PA::$url . PA_ROUTE_CONTENT . "/cid=".$links[$i]['content_id'].$gid;
               $img_tag = '<img src="'.$links[$i]['image_src'].'" width="35px" height="30px" border="0" class="img_list" />';
          } else if ( strstr( $links[$i]['image_file'], "http://")) {
              $image_path = $links[$i]['image_file'];
                 //Verify image path as well as Image type
              $image_path = (verify_image_url($image_path)) ? $links[$i]['image_file'] : PA::$theme_url . '/images/no_img_found.gif';
              $img_tag = '<img src="'.$image_path.'" width="35px" height="30px" border="0" class="img_list" />';
              $image_hyperlink = PA::$url . "/media_full_view.php?cid=".$links[$i]['content_id'].$gid;
          } else {
              $img_tag = uihelper_resize_mk_img($links[$i]['image_file'], 35, 30,NULL,'alt="PA" class="img_list"');
              $image_hyperlink = PA::$url . "/media_full_view.php?cid=".$links[$i]['content_id'].$gid;
          }
          /* Links for editing content */
           if($links[$i]['type'] == 7) {
                $link_for_editing = PA::$url . "/post_content.php?cid=".$links[$i]['content_id'];
                $content_type = 7;
            } else {
                $link_for_editing = PA::$url . "/edit_media.php?uid=".PA::$login_uid."&amp;cid=".$links[$i]['content_id'];
                $content_type = 1;
            }

  ?>

  <tr>
    <td>
    <?php if($uid == PA::$login_uid && empty($_GET['gid'])) { ?>

      <a href="<?php echo $link_for_editing;?>"><img src="<?php echo PA::$theme_url;?>/images/16_edit.gif" alt="edit" height="16" width="16" border="0" title="Edit" /></a>
      <a href="#"><img src="<?php echo PA::$theme_url;?>/images/16_delete.gif" alt="delete" title="Delete" height="16" width="16" border="0"  onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload');" /></a>

    <? } if (isset($_GET['gid']) && !empty ($_GET['gid']) && ($links[$i]['author_id'] == PA::$login_uid )) {

       if ($links[$i]['type'] == 7) {
         $link_for_editing = PA::$url . "/post_content.php?cid=".$links[$i]['content_id'];
       } else {
         $link_for_editing = PA::$url . "/edit_media.php?uid=".PA::$login_uid."&amp;cid=".$links[$i]['content_id']."&amp;type=Images";
       }
    ?>

      <a href="<?php echo $link_for_editing;?>"><img src="<?php echo PA::$theme_url;?>/images/16_edit.gif" alt="edit" height="16" width="16" border="0" /></a>
      <a href="#"><img src="<?php echo PA::$theme_url;?>/images/16_delete.gif" alt="delete" height="16" width="16" border="0"  onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload');" /></a>

    <?}?>
     </td>
    <td>
      <div align="center">
        <a href="<?php echo $image_hyperlink; ?>">
          <?=$img_tag?>
        </a>
      </div>

   </td>
   <td>
      <ul>
        <li><h1><a href="<?php echo $image_hyperlink; ?>">
                 <?=wordwrap(ucwords(strtolower(_out($links[$i]['title']))), LONGER_CHUNK_LENGTH, " ", 1);?>
              </a></h1></li>
        <li><a href="<?=$image_hyperlink;?>">
          <?php
            if (isset($links[$i]['body'])) {
              $links[$i]['body'] = _out($links[$i]['body']);
              if(strlen($links[$i]['body']) > 30) {
                $ext = substr ($links[$i]['body'], -2);
                $start =  substr ($links[$i]['body'], 0, 29);
                print stripslashes($start."....".$ext);
              } else {
                print stripslashes($links[$i]['body']);
              }
            }
          ?>
         </a>
       </li>
        <li>
          <div class="post_info">
            <b>Created:</b> <?=content_date($links[$i]['created']);?>
            <?php if (!empty($links[$i]['tags'])) { ?>
             <b>Tags:<?=$links[$i]['tags'];?></b>
            <? } ?>

          </div>
        </li>
      </ul>
    </td>
    <td>
      <?php
        if($gid) {
          $album_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/view=groups_media" . $gid;
        } else {
          $album_url = PA::$url . PA_ROUTE_MEDIA_GALLEY_IMAGES . "/uid=" . $uid;
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
        <?= __("No Photos.") ?>
     </td>
   </tr>
  <? } ?>
</table>
</div>
