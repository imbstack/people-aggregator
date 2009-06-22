<div class="module_browse_groups">
  <ul>
  <?php for ( $i = 0; $i < count ( $newarray ); $i++ ) { ?>
   <li><a href="#" onclick="javascript:show_hide_network_categories('id_<?php echo $i;?>','arrow_close_<?php echo $i;?>'); return false;"><?php echo $newarray[$i]['cat_name'];?></a> <?php echo $newarray[$i]['members'];?>
      
     <ul id="id_<?php echo $i;?>" class="display_false">
         
         <?php  if ( $newarray[$i]['members'] > 0 ) {
          for( $j = 0; $j < $newarray[$i]['members']; $j++ ) { 
           $network_name=chop_string(stripslashes($newarray[$i]['networks_info'][$j]['network_name']), 18);?>
      
      <li>
        <a href="http://<? echo $newarray[$i]['networks_info'][$j]['address'] .'.' . PA::$domain_suffix . BASE_URL_REL . PA_ROUTE_HOME_PAGE ;?>"><?php echo $network_name;?></a>
      </li>

      <?} } else {?>
        <li><?= __("No networks in this category") ?></li>
      <?  } ?> 
     
     </ul>
   
   </li>
  <? } ?> 
  
  </ul>
</div>