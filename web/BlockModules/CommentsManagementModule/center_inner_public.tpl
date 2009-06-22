<div class="description"><?= __("Manage comments.") ?></div>
<?php  
  // global var $_base_url has been removed - please, use PA::$url static variable

?>  
<form name="manage_contents" method="post" action="">
  <fieldset class="center_box">
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?> <div class="mininav">
<?php if($links) { ?>
<div class="listtable">
<table id="tablelist" width="100%" cellpadding="3" cellspacing="3"> 
  <tr>

  <td><b><?= __("When") ?></b></td>
  <td><b><?= __("Title") ?></b></td>
  <td><b><?= __("Parent") ?></b></td>
  <td><b><?= __("Abuses") ?></b></td>
  <td><b><?= __("Author") ?></b></td>
  <td></td>
  </tr>
<?php for( $i = 0; $i < count( $links ); $i++) { ?>
  <tr class='alternate'>
    <td><?=date("F d Y", $links[$i]['time']);?></td>
    <td><a href="<?php echo $links[$i]['hyper_link'];?>"><?php echo chop_string($links[$i]['comment'], 20);?></a></td>
    <td><?php echo chop_string($links[$i]['comment_title'],20);?></td>
    <td><?php echo $links[$i]['abuses'];?></td>
    <td><?php echo $links[$i]['author_name'];?></td>
    <td><a href="<?php echo $links[$i]['delete_url'];?>" class='edit' onclick="javascript:return delete_content1();"><?= __("Delete") ?></a></td>
 </tr> 
<?php } ?>
  
</table>
</div>
<?php } else {?> 
<div class="required"><?= __("No Comments") ?></div>
<?php } ?>
</div>  

   
<?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>       

  </fieldset>
<?php echo $config_navigation_url; ?>
</form>