<?php  global $login_uid, $current_theme_path;?>

<h1>Tag Search</h1>

<form action="<?php echo PA::$url;?>/tag_base_search.php" method="get">
  <fieldset class="center_box">
    <legend><?= __("Tag Search") ?></legend>
    <div class="field">
    Search for:<input type="text" value ="<?php echo stripslashes(@$_GET['keyword']);?>" name="keyword"/>
      <select class="select-txt" name="name_string">
        <?php 
          foreach ( $search_str as $search_option ) {
             if (@$_GET['name_string'] == $search_option['value']) {
                echo "<option value=\"".$search_option['value']."\" selected >".$search_option['caption'].'</option>'; 
             }
             else {
                echo "<option value=\"".$search_option['value']."\">".$search_option['caption'].'</option>';  
             }             
           }
        ?>
      </select>
    <input type = "image" src="<?echo $current_theme_path;?>/images/go-btn.gif" />
    </div>
  </fieldset>  
</form>


<?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
  <?php } ?>
  
  <?php  $cnt = count($links);
  if (  $cnt > 0) {?>
    <?php
      if(!empty($links['content_info'])) {
        require_once 'content_info.tpl';
      } else if(empty($links['content_info']) && key($links) == 'content_info') {
        echo "<br /><br />  No Content is matching your keyword.";
      }
      if(!empty($links['user_info'])) {
        require_once 'user_info.tpl';
      } else if(empty($links['user_info']) && key($links) == 'user_info') {
        echo "<br /><br />   No user is matching your keyword.";
      }
      if(!empty($links['group_info'])) {
        require_once 'group_info.tpl';
      } else if(empty($links['group_info']) && key($links) == 'group_info') {
        echo "<br />   No group is matching your keyword.";
      }
    ?>
    <?  } else { ?>      
          <br/>Enter a keyword
    <? } ?>
  <?php if( $page_links ) {?>
   <div class="prev_next">
     <?php if ($page_first) { echo $page_first; }?>
     <?php echo $page_links?>
     <?php if ($page_last) { echo $page_last;}?>
   </div>
 <?php } ?>
