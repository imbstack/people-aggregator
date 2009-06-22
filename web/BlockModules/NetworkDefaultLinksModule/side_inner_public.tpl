<?php
  global $current_theme_path;
  // global var $_base_url has been removed - please, use PA::$url static variable

 
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
    
    <li<?php echo $class;?>><a href="#" ><img src="<?php echo $current_theme_path;?>/images/minus.gif" alt="ps" id="imagee_<?php echo $counter;?>" name="zz" onclick="javascript:show_hide_network_default_categories('link_id_<?php echo $counter;?>','plus.gif','imagee_<?php echo $counter;?>'); return false;" /></a><a href="#" onclick="javascript:show_hide_network_default_categories('link_id_<?php echo $counter;?>','plus.gif','imagee_<?php echo $counter;?>'); return false;">   
    <?php echo ucfirst(chop_string($links_data_array[$counter]['category_name'], 30)) ?></a>
      
      <ul id="link_id_<?php echo $counter;?>" class="display_true">
      <?php 
        $links = count($links_data_array[$counter]['links']); 
        if($links > 0) {
          for($link_counter = 0; $link_counter < $links; $link_counter++) {
      ?>
              
        <li>
          <a href="<?php echo $links_data_array[$counter]['links'][$link_counter]->url;?>"  target='blank'>
           <?php echo  chop_string($links_data_array[$counter]['links'][$link_counter]->title, 30);?>
          </a>
        </li>
      
       <?php 
          }  }
        else { ?> <li><?= __("No links under this category") ?></li><? } ?>

        </ul>
      </li> 
  <?php 
    }
   ?>
   </ul>
   
   <?php 
  }  
  else {
      echo __("No links have been added.");
  } ?>
</div>
