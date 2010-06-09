<?php
$local_image_variable = FALSE;
/*
if ($type !='forum') {
  $button['title'] = 'Manage forum';
  $button['type'] = 'forum';
} else {
  $button['title'] = 'Manage content';
  $button['type'] = 'all';
}
*/
$back_url = '&back_page=' . urlencode($back_page . ((isset($_GET['gid'])) ? '?gid='.$_GET['gid'] :''));

?>
<div class="description"><?= sprintf(__("On this page you can manage all posts of the %s."), PA::$group_noun)?></div>
<form enctype="multipart/form-data" name="delete_content" action="" method="post">
<input type="hidden" name="back_page" id="back_page" value="<?php echo $back_url ?>" />

<div id="buttonbar">
<ul>
  <li><a href="<?php echo PA::$url;?>/post_content.php?ccid=<?php echo @$_GET['gid']?>"><?= __("Create post") ?></a></li>

<!--
  <li><a href="<?php echo PA::$url;?>/manage_group_content.php?gid=<?php echo $_GET['gid'];?>&amp;type=<?php echo $button['type'];?>"><?php echo __($button['title']);?></a></li>
-->

</ul>
</div>
<?php if($page_links) {?>
 <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>
<div class="table">

<?php $cnt = count($links) ;
   if ($cnt) { ?>
<table  cellspacing="0" cellpadding="0" >

<tr>
<th width="50"></th>
<th width="410">Title</th>
<th width="40">Type</th>
<th width="100">Date</th>
</tr>
   <?php for ($i=0; $i < $cnt; $i++) {

   ?>
  <tr>
   <td>
     <a href="<?php echo $links[$i]->edit_link; ?>"><img src="<?php echo PA::$theme_url;?>/images/16_edit.gif" alt="edit" title="<?= __("Edit") ?>" height="16" width="16" border="0" /></a>
     <a href="<?php echo $links[$i]->delete_link . $back_url; ?>"><img src="<?php echo PA::$theme_url;?>/images/16_delete.gif" alt="delete" height="16" width="16" border="0" title="Delete" /></a>
   </td>
   <td><a href="<?php echo $links[$i]->hyper_link; ?>">
     <?=chop_string($links[$i]->title);?> </a>
   </td>
     <?php if (empty($type_image) || !empty($local_image_variable)) {
                  if ($links[$i]->content_type == 'BlogPost') {
                    $type_image = PA::$theme_url."/images/type-blog.gif";
                    $local_image_variable=TRUE;
                  }
                  if ($links[$i]->content_type == 'Image') {
                    $type_image = PA::$theme_url."/images/type-image.gif";
                    $local_image_variable=TRUE;
                  }
                  if ($links[$i]->content_type == 'Audio') {
                    $type_image = PA::$theme_url."/images/type-audio.gif";
                    $local_image_variable=TRUE;
                  }
                  if ($links[$i]->content_type == 'Video') {
                    $type_image = PA::$theme_url."/images/type-video.gif";
                    $local_image_variable=TRUE;
                  }
            }
   ?>
   <td><img src="<?=$type_image;?>" alt="PA" /></td>
   <td><?php print manage_content_date($links[$i]->changed);?></td>
   </tr>
   <? } ?>
   </table>
   <? } ?>
  <? if(empty($links)) { ?>
    <table  cellspacing="0" cellpadding="0" >
      <tr><th><div class="description"> <?= __("No Content Published") ?></div></th></tr>
    </table>
   <? } ?>

<input type="hidden" name="delete_type" id="delete_type" value="" />
 </form>
</div>
