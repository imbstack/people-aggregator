<?php
 //The sorting link will be formed for created, group name and categories caption in the group listing.
 //default error message.
 $message = 'No group created yet.';
 $created_link = PA::$url.'/'.FILE_MANAGE_GROUPS.'?sort_by=created';
 $name_link = PA::$url.'/'.FILE_MANAGE_GROUPS.'?sort_by=name';
 $category_link = PA::$url.'/'.FILE_MANAGE_GROUPS.'?sort_by=category';

 if (!empty($search_str)) {
   //default message for group search
   $message = 'No groups matched your search.';
   $created_link .= '&amp;search_str='.$search_str;
   $name_link .= '&amp;search_str='.$search_str;
   $category_link .= '&amp;search_str='.$search_str;
 }

 if (!empty($sort_by)) {
   switch ($sort_by) {
     case 'created':
       if ($sort_dir == 'DESC') {
         $created_link .= '&amp;sort_dir=ASC';
       }
       $created_link = '<a href="'.$created_link.'"><b>Created</b></a>';
       $name_link = '<a href="'.$name_link.'">Group Name</a>';
       $category_link = '<a href="'.$category_link.'">Category</a>';
     break;
     case 'name':
       if ($sort_dir == 'DESC') {
         $name_link .= '&amp;sort_dir=ASC';
       }
       $created_link = '<a href="'.$created_link.'">Created</a>';
       $name_link = '<a href="'.$name_link.'"><b>Group Name</b></a>';
       $category_link = '<a href="'.$category_link.'">Category</a>';
     break;
     case 'category':
       if ($sort_dir == 'DESC') {
         $category_link .= '&amp;sort_dir=ASC';
       }
       $created_link = '<a href="'.$created_link.'">Created</a>';
       $name_link = '<a href="'.$name_link.'">Group Name</a>';
       $category_link = '<a href="'.$category_link.'"><b>Category</b></a>';
     break;
   }
 } else {
   $created_link = '<a href="'.$created_link.'">Created</a>';
   $name_link = '<a href="'.$name_link.'">Group Name</a>';
   $category_link = '<a href="'.$category_link.'">Category</a>';
 }

?>
<div class="description"><?= __("In this page you can manage groups on your network") ?></div>
<div style="text-align:center;width:100%;">

<form action="<?php echo PA::$url.$_SERVER['REQUEST_URI']?>" name="formSearchGroups">
  <fieldset class="center_box">
    <legend>Search Groups</legend><input name="search_str" value="<?php echo htmlspecialchars(@$search_str); ?>" type="text" size="18" />
   <input name="search" type="submit" id="search" value="Search" />
   <input name="clear" type="button" id="clear" value="Clear Search" onclick="document.location=base_url+'/<?php echo FILE_MANAGE_GROUPS?>'" />
  </fieldset>
</form>

<form name="manage_users" method="post" action="">
  <fieldset class="center_box">
  <?php if ($page_links) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>
     <?php if (!empty($links)) {
       $link_count = count($links);
     ?>
     <table cellpadding="3" cellspacing="3" width="100%">
      <?php
        if (!empty($search_str)) {
      ?>
      <tr><td colspan="6" align="center"><b><u><?php echo $link_count?> groups matched your search</u></b></td></tr>
      <?php
        }
      ?>
      <tr>
        <td>Select</td>
        <td>Picture</td>
        <td><?php echo $created_link?></td>
        <td><?php echo $name_link?></td>
        <td><?php echo $category_link?></td>
        <td>Action</td>
      </tr>
         <?php foreach ($links as $link) {?>
             <tr class='alternate' style="background:#ccc;">
                <td><input type="checkbox" name="gid[]" value="<?php echo $link['group_id']; ?>" /></td>
               <td><a href="<?php echo PA::$url . PA_ROUTE_GROUP . "/gid=" . $link['group_id'];?>"><?php echo uihelper_resize_mk_img($link['picture'], 35, 35, "images/default_group.gif", 'alt="group image"', RESIZE_FIT);?></a></td>
               <td><?php echo PA::date($link['created'] ,'short') // date('Y-M-d', $link['created']);?></td>
               <td><a href="<?php echo PA::$url . PA_ROUTE_GROUP . "/gid=" . $link['group_id'];?>"><?php echo chop_string($link['title'], 25);?></a></td>
               <td><?php echo chop_string($link['name'], 25);?></td>
               <td><a href="<?php echo PA::$url;?>/manage_groups.php?action=delete&delete_gid=<?php echo $link['group_id'];?>" onclick="javascript: return delete_confirmation_msg('Are you sure you want to delete this group ?');" class='delete'>Delete</a></td>
             </tr>
        <?php } ?>
         <tr>
            <td colspan="6" style="text-align:left;"><input type="checkbox" name="check_uncheck" onclick='javascript: check_uncheck_all("manage_users", "check_uncheck");'>(un)check all &nbsp;<a href="#" onclick = "javascript: delete_selected_groups();return false;">Delete selected</a></td>
         </tr>
      </table>

    <?php } else {?>
    <div class ="required"><?php echo $message?></div>
    <?php }?>

<?php if ($page_links) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links;?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
<?php
  }
?>

  </fieldset>
<?php echo $config_navigation_url; ?>
</form>
</div>
