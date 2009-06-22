<?php 
  global $login_uid, $current_theme_path, $uid;
  $count = count($links);  
  $curr_user_id = (isset($_SESSION['user']['id'])) ? $_SESSION['user']['id'] : -1;
 ?>
<div class="media_gallery_thumb" id="image_gallery_thumb">
<?php if ($count > 0) { ?>
<ul>
<?php for($i = 0; $i < $count; $i++) {?>   
  <li>
    <?php  // for Images and their hyperlinks
       if ( $links[$i]['type'] == 7) {
         $image = "<img src='".$current_theme_path."/images/sb-70X50.jpg' width='70px' height='38px' border='0' alt='SB'/>";
         $hyper_link = PA::$url . PA_ROUTE_CONTENT . '/cid='.$links[$i]['content_id'];
       }
       else {
         $image = uihelper_resize_mk_img("files/".$links[$i]['internal_thumbnail'], 80, 70, "images/video_avail_soon.gif", 'alt="'.$links[$i]['title'].'"', RESIZE_FIT);
         $hyper_link = PA::$url.'/media_full_view.php?cid='.$links[$i]['content_id'];
         if(isset($gid)) $hyper_link .= "&amp;gid=$gid";  // if Group Media gallery
      }
    ?>
     <a href="<?=$hyper_link?>"> <?php echo $image;?> </a>
     
     <p class="name">
        <a href="<?=$hyper_link?>" >
           <?php 
           $title = _out($links[$i]['title']);
           if(strlen($title) > 10) { $ext = substr ($title, -2); $start =  substr ($title, 0, 9); print stripslashes($start."....".$ext); } else { print stripslashes($title); }?>
         </a>
     </p>
     
    <?php if ($uid == $curr_user_id && empty($_GET['gid'])) { 
              if($links[$i]['type'] == 7) {
                  $link_for_editing = PA::$url . "/post_content.php?cid=".$links[$i]['content_id'];
              } else {
                  $link_for_editing = PA::$url . "/edit_media.php?uid=".$uid."&amp;cid=".$links[$i]['content_id']."&amp;type=video";
              }
    ?>         
     <p class="choose">

       <a href="<?php echo $link_for_editing;?>"><img src="<?php echo $current_theme_path;?>/images/16_edit.gif" alt="" height="16" width="16" border="0" title="Edit"></a> 
       <a href="#"><img src="<?php echo $current_theme_path;?>/images/16_delete.gif" alt="" height="16" width="16" border="0"  title="Delete" onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload2'); return false;"></a>
    </p>
    
    <? } if (isset($_GET['gid']) && !empty ($_GET['gid']) && ($links[$i]['author_id'] == $login_uid )) {
       if($links[$i]['type'] == 7) {
                    $link_for_editing = PA::$url . "/post_content.php?cid=".$links[$i]['content_id'];
                } else {
                    $link_for_editing = PA::$url . "/edit_media.php?uid=".$login_uid."&amp;cid=".$links[$i]['content_id']."&amp;type=video";
                }
    ?>
     <p class="choose">

       <a href="<?php echo $link_for_editing;?>"><img src="<?php echo $current_theme_path;?>/images/16_edit.gif" alt="" height="16" width="16" border="0"></a> 
       <a href="#"><img src="<?php echo $current_theme_path;?>/images/16_delete.gif" alt="" height="16" width="16" border="0"  onclick="edit_delete_media('<?=$links[$i]['content_id']?>','delete','image_upload2');"></a>
    </p>
       
    <?}?>
  </li>
<?} // End of for loop?>  
</ul>
<?} else { ?>
  <ul>
    <li>
      <?= __("No Video") ?>
    </li>
  </ul>  
<? } ?>
</div>