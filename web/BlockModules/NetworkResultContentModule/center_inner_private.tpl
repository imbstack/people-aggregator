<?php
?>
<div class="description"><?= __("Manage content on your network.") ?></div>
<?php
  // global var $_base_url has been removed - please, use PA::$url static variable

  //current month's first date
  $current_month = mktime(0, 0, 0, date("m"), 1,  date("Y"));
  //previous month's first date
  $previous_month = mktime(0, 0, 0, date("m")-1, 1,  date("Y"));
  //previous to previous month's first date
  $pre_previous_month = mktime(0, 0, 0, date("m")-2, 1,  date("Y"));

  $array_month = array($current_month, $previous_month, $pre_previous_month);
?>

<form  action="<?php echo PA::$url;?>/network_manage_content.php">
  <fieldset class="center_box">
  <legend><?= __("Search posts") ?> </legend>
    <input name="keyword" value="<?php echo htmlspecialchars(@$_GET['keyword']); ?>" type="text" size="18" />
  <h3><?= __("Browse months") ?> </h3>
    <select name="select_month">
        <?  for ( $i = 0; $i < count($array_month); $i++ ) {
             if (@$_GET['select_month'] == $array_month[$i]) {
                echo "<option value=\"".$array_month[$i]."\" selected >".date( 'M Y',$array_month[$i] ).'</option>';
             }
             else {
                echo "<option value=\"".$array_month[$i]."\">".date( 'M Y',$array_month[$i] ).'</option>';
             }
           }
        ?>
      </select>
        <input name="search" type="submit" id="search" value="<?= __("Search") ?>" />
  </fieldset>
</form>
<form name="manage_contents" method="post" action="<?php echo PA::$url .'/network_manage_content.php'?>">
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
<?php if($links) {?>
<div class="listtable">
<table id="tablelist" width="100%" cellpadding="2" cellspacing="2">
	<tr>

	<td><b><?= __("When") ?></b></td>
	<td><b><?= __("Title") ?></b></td>
	<td><b><?= __("Type") ?></b></td>
  	<td><b><?= __("Parent") ?></b></td>
	<td><b><?= __("Comments") ?></b></td>
 	<td><b><?= __("Abuses") ?></b></td>
	<td><b><?= __("Author") ?></b></td>
  	<td></td>
	<td></td>
	</tr>
<?php for( $i = 0; $i < count( $links ); $i++) {?>
  <tr class='alternate'>
    <?php $delete_url = PA::$url .'/deletecontentbynetadmin.php?cid='.$links[$i]['content_id'];?>
    <td><?php echo $links[$i]['created'];?></td>
    <td><a href="<?php echo $links[$i]['hyper_link'];?>"><?php echo chop_string($links[$i]['title'], 20);?></a></td>
    <td><?php echo $links[$i]['type'];?></td>
    <td><?php echo @$links[$i]['parent_name']; // can apparently be empty ?></td>
    <td><?php echo $links[$i]['comment_count'];?></td>
    <td><?php echo $links[$i]['abuses'];?></td>
    <td><a href="<?php echo $links[$i]['author_home_url'];?>" title="<?php echo $links[$i]['author_name'];?>" alt="<?php echo $links[$i]['author_name'];?>"><?php echo $links[$i]['author_name'];?></a></td>
    <td><a href="<?php echo $links[$i]['edit_link'];?>" class='edit'>Edit</a></td>
    <td><a href='<?php echo $delete_url?>' onclick="javascript: return delete_confirmation_msg('<?= __("Are you sure you want to delete this content?") ?>');" class='delete'><?= __("Delete") ?></a></td>
 </tr>
<?php } ?>

</table>
</div>
<?php } else {?>
<div class="required"><?= __("No Content.") ?></div>
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






