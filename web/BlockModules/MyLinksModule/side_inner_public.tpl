<?php
   
  global $login_uid,$page_uid;
?>  
<div class="module_browse_groups">
<?php  
  $category_count = count($links_data_array);
  if($category_count > 0) {
?>
  <ul>
  <?php 
    for ( $counter = 0; $counter < count($links_data_array); $counter++ ) {
      $class = (( $counter%2 ) == 0) ? ' class="color"': NULL;
  ?>
    
    <li<?php echo $class;?>><a href="#" onclick="javascript:show_hide_network_categories('link_id_<?php echo $counter;?>','arrow_close_1'); return false;">   
    <?php echo ucfirst(chop_string($links_data_array[$counter]['category_name'], 23)) ?></a>
      
      <ul id="link_id_<?php echo $counter;?>" class="display_true">
      <?php 
        $links = count($links_data_array[$counter]['links']); 
        if($links > 0) {
          for($link_counter = 0; $link_counter < $links; $link_counter++) {
      ?>
              
			  <li>
          <a href="<?php echo $links_data_array[$counter]['links'][$link_counter]->url;?>"  target='blank'>
           <?php echo  chop_string($links_data_array[$counter]['links'][$link_counter]->title, 23);?>
          </a>
        </li>
      
			 <?php 
          }  }
        else { ?> <li> <?= __("No links under this category") ?> </li><? } ?>

        </ul>
      </li> 
  <?php 
    }
   ?>
   </ul>
   
   <?php 
  }  
  else {
    if (!isset($page_uid) || $login_uid == $page_uid) { ?>
      <?= sprintf(__('No links have been added. Click <a href="%s">here</a> to add links.'), PA::$url."/links_management.php") ?>
    <? } else { ?>
      <?= __("No links have been added") ?>
    <? } 
  } ?>
</div>
