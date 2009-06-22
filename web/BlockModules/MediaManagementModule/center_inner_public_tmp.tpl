<?php
global $current_theme_path;
      $local_image_variable = FALSE;
?>  

<ul id="filters">
  <li><a href="<?php echo PA::$url;?>/content_management.php"><?= __("All Posts") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=blog"><?= __("Blog") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=event"><?= __("Events") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=review"><?= __("Reviews") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=people_showcase"><?= __("People") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=video"><?= __("Video") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=audio"><?= __("Audio") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=image"><?= __("Image") ?></a> | </li>
  <li><a href="<?php echo PA::$url;?>/content_management.php?type=group_showcase"><?= __("Group") ?></a></li>
</ul>
  
<div id="content_management">
  <form enctype="multipart/form-data" name="delete_content" action="content_management.php?action=delete&amp;uid=<?=$uid?>" method="post" onsubmit="return delete_media_content(document.delete_content);">
  <ul>
    <li> <input type="submit" id="delete_content_upper" name="submit" value="<?= __("Delete") ?>"/></li>
  </ul>
  
   <?php if(count($links)) { ?> 
 <table id="tablelist" width="100%" cellpadding="3" cellspacing="3">
  <tr>
    <td></td>
    <td><?= __("Edit") ?></td>
    <td><?= __("Title") ?></td>
    <td><?= __("Type") ?></td>
    <td><?= __("Date") ?></td>
  </tr>
   <?php for ($i=0; $i < count($links); $i++) {
      $linkForEditing = PA::$url ."/post_content.php?cid=".$links[$i]['content_id'];
   ?>
   <tr>
   <td>
     <input type="checkbox" name="delete_content[]" value="<?=$links[$i]['content_id'];?>"/>
   </td>
   <td><a href="<?php echo $linkForEditing; ?>">
     <img src="<?=$current_theme_path;?>/images/edit.png" alt="PA" border="0"/></a>
   </td>
   <td><a href="<?= PA::$url . PA_ROUTE_CONTENT . '/cid=' . $links[$i]['content_id'] ?>">
     <?=chop_string($links[$i]['title']);?> </a>
   </td>
     <?php if (!$type_image || $local_image_variable) {
                  if ($links[$i]['type_name'] == 'BlogPost') {
                    $type_image = $current_theme_path."/images/type-blog.gif";
                    $local_image_variable=TRUE;
                  }

            } 
   ?>   
   <td><img src="<?=$type_image;?>" alt="PA" /></td>
   <td><?php print manage_content_date($links[$i]['changed']);?></td>
   </tr>
   <? } ?>
   </table>
   <? } ?>  

  
  <? if(empty($links)) { ?>
    <ul>
      <li>
        <?= __("No Content Published.") ?>
      </li>
    </ul> 
  <? } ?>
  
  <ul>
    <li> <input type="submit" id="delete_content_lower" name="submit" value="<?= __("Delete") ?>"/></li>
  </ul>
 <input type="hidden" name="delete_type" id="delete_type" value="" />
 </form>
</div>