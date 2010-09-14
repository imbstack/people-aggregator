<div class="description"><?= __("Manage suggesions on your network.") ?></div>
<form name="manage_users" method="post" action="<?php echo PA::$url.'/network_moderate_content.php'?>">
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
    <td><b><?= __("Select") ?></b></td>
    <td><b><?= __("When") ?></b></td>
    <td><b><?= __("Title") ?></b></td>
    <td><b><?= __("Type") ?></b></td>
    <td><b><?= __("Parent") ?></b></td>
    <td><b><?= __("Author") ?></b></td>
    <td></td>
    <td></td>
  </tr>
<?php for( $i = 0; $i < count( $links ); $i++) {?>
  <tr class='alternate'>   
    <td><input type="checkbox" name="cid[]" value="<?php echo $links[$i]['content_id']; ?>" /></td>
    <td><?php echo $links[$i]['created'];?></td>
    <td><a href="<?php echo $links[$i]['hyper_link'];?>"><?php echo chop_string($links[$i]['title'], 20);?></a></td>
    <td><?php echo $links[$i]['type'];?></td>
    <td><?php if(isset($links[$i]['parent_name'])) echo $links[$i]['parent_name'];?></td>
    <td><a href="<?php echo $links[$i]['user_url'];?>"><?php echo $links[$i]['author_name'];?></td>
    <td><a href="<?php echo $links[$i]['approve_link'];?>">Approve</a></td>
    <td><a href="<?php echo $links[$i]['deny_link'];?>">Deny</a></td>
  </tr> 
<?php } ?>
  <tr>
    <td colspan="3">
        <select name="action" id="act">
        <option value="">--- <?= __("Select") ?> ---</option>
        <option value="deny"><?= __("Deny") ?></option>
        <option value="approve"><?= __("Approve") ?></option>                
      </select> Selected <input type="submit" name="submit" value="<?= __("Go") ?>">
    </td>
    <td colspan="4"></td>
  </tr>
  <tr>
    <td colspan="7"><input type="checkbox" name="check_uncheck" onclick='javascript: check_uncheck_all("manage_users", "check_uncheck");'>(un)check all</td>
  </tr>	
</table>
</div>
<?php } else {?> 
<div class="required"><?= __("No Content For Moderation.") ?></div>
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






