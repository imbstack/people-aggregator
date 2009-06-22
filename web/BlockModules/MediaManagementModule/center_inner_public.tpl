<?php 
// global var $_base_url has been removed - please, use PA::$url static variable

global $current_theme_path;
$local_image_variable = FALSE;
// todo this array and highlight logic is used in 3 places
// move it to one place
$links_array = array(
                       1=>array('post_type'=>'blog',
                                'title'=>'Blog'
                               ),
                       2=>array('post_type'=>'event',
                                'title'=>'Event'
                               ),
                       3=>array('post_type'=>'review',
                                'title'=>'Review'
                               ),
                       4=>array('post_type'=>'people_showcase',
                                'title'=>'People'
                               ),
                       5=>array('post_type'=>'video',
                                'title'=>'Video'
                               ),
                       6=>array('post_type'=>'audio',
                                'title'=>'Audio'
                               ),
                       7=>array('post_type'=>'image',
                                'title'=>'Image'
                               ),
                       8=>array('post_type'=>'group_showcase',
                                'title'=>'Group'
                               )
                      );
                      

?>  
<div class="description"><?= __("On this page you can manage all posts you have created.") ?></div>

  


<form enctype="multipart/form-data" name="delete_content" action="content_management.php?action=delete&amp;uid=<?=$uid?>" method="post" >

<div id="buttonbar">
<ul>
<li><a href="<?php echo PA::$url;?>/post_content.php"><?= __("Create post") ?></a></li>  </ul>
</div>
<div class="table">
<?php 
global $_PA;
if (empty($_PA->simple['use_simpleblog'])) { 
?>
<ul id="filters">

  <?php $get_post_type = @$_GET['type']; 
        if (empty($get_post_type)) {
          $highlight = '';
        } else {
          $highlight = 'class="active"';
        }
  ?>
    <li <?php if (empty($get_post_type) || ($get_post_type == 'all')){ echo 'class="active"';}?>><a href="<?php echo PA::$url .'/content_management.php';?>">All Posts
    </a></li>
 <?php foreach ($links_array as $link) { ?>
      <li <?php if ($get_post_type==$link['post_type']) { echo $highlight;}?>
      ><a href="<?php echo
      PA::$url .'/content_management.php?type='.$link['post_type'];?>">
      <?php echo $link['title'];?></a></li>
    <?php } ?>
</ul>
<? } ?>
<?php if(count($links)) { ?> 
<table  cellspacing="0" cellpadding="0" >

<tr>
<th width="50"></th>
<th width="410">Title</th>
<th width="40">Type</th>
<th width="100">Date</th>
</tr>
   <?php for ($i=0; $i < count($links); $i++) {
      $linkForEditing = PA::$url."/".FILE_POST_CONTENT."?cid=".$links[$i]['content_id'];
      $linkForDeleting = PA::$url . PA_ROUTE_CONTENT . "?action=deleteContent&cid=" . $links[$i]['content_id'].'&back_page=' . urlencode(PA::$url.'/content_management.php');
   ?>
  <tr>
   <td>
     <a href="<?php echo $linkForEditing; ?>"><img src="<?php echo $current_theme_path;?>/images/16_edit.gif" alt="edit" title="Edit" height="16" width="16" border="0" /></a>
     <a href="#" onclick="return delete_media_content(document.delete_content,'<?php echo $linkForDeleting;?>');" ><img  src="<?php echo $current_theme_path;?>/images/16_delete.gif" alt="delete" height="16" width="16" border="0" title="Delete" /></a>
   </td>
   <td><a href="<?= PA::$url . PA_ROUTE_CONTENT . "/cid=" . $links[$i]['content_id'] ?>">
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
    <div class="description"> <?= __("No Content Published") ?></div>

  
   <?}?>

<input type="hidden" name="delete_type" id="delete_type" value="" />
 </form>
</div>