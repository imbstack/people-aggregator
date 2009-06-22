<?php 
// global var $_base_url has been removed - please, use PA::$url static variable

global $current_theme_path;
$local_image_variable = FALSE;
if ($type !='forum') {
  $button['title'] = 'Manage forum';
  $button['type'] = 'forum';
} else {
  $button['title'] = 'Manage content';
  $button['type'] = 'all';
}

?>
<div class="description"><?= __("On this page you can manage all the forums of the Group.") ?></div>
<form enctype="multipart/form-data" name="delete_content" action="" method="post">

<div id="buttonbar">
<ul>
  <li><a href="<?php echo PA::$url;?>/create_forum_topic.php?gid=<?php echo @$_GET['gid']?>"><?= __("Create Forum") ?></a></li>
  <li><a href="<?php echo PA::$url;?>/manage_group_content.php?gid=<?php echo $_GET['gid'];?>&amp;type=<?php echo $button['type'];?>"><?php echo __($button['title']);?></a></li>
</ul>
</div>
<?php if ($page_links) { ?>
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
   <?php for ($i=1; $i <= $cnt; $i++) { ?>
  <tr>
   <td>
     <a href="<?php echo $links[$i]['edit_link']; ?>"><img src="<?php echo $current_theme_path;?>/images/16_edit.gif" alt="edit" title="<?= __("Edit") ?>" height="16" width="16" border="0" /></a>
     <a href="<?php echo $links[$i]['delete_link']; ?>"><img src="<?php echo $current_theme_path;?>/images/16_delete.gif" alt="delete" height="16" width="16" border="0" title="Delete" /></a>
   </td>
   <td><a href="<?php echo $links[$i]['hyper_link']; ?>">
     <?=chop_string($links[$i]['title']);?> </a>
   </td>
     <?php if (empty($type_image) || !empty($local_image_variable)) {
               $type_image = $current_theme_path."/images/type-blog.gif";
            } 
   ?>   
   <td><img src="<?=$type_image;?>" alt="PA" /></td>
   <td><?php print manage_content_date($links[$i]['created']);?></td>
   </tr>
   <? } ?>
   </table>
   <? } ?>   
  <? if(empty($links)) { ?>
    <div class="description"> <?= __("No Content Published") ?></div>

  
   <?}?>

<input type="hidden" name="delete_type" id="delete_type" value="" />
 </form>
</div>