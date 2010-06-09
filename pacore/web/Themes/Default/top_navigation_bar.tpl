<?php  
  $level_1 = $navigation_links['level_1'];
  unset($level_1['highlight']);
  if (PA::$network_info->type == MOTHER_NETWORK_TYPE) {
    $caption = sprintf(__("%s Networks"), PA::$site_name);
  }
  else {
    $caption = ucfirst(PA::$network_info->name).' Network';
  }
?>
<?php
  $mother_network = Network::get_mothership_info(); 
  $extra = unserialize($mother_network->extra);
  if (!(array_key_exists('top_navigation_bar', $extra)) || ($extra['top_navigation_bar'] == NET_YES)) { // check if top navigation bar is required or not
?>
<div id="peopleaggregator_networks">
  <div class="peopleaggregator_networks_dimension">
    <h1><a href="<?= PA::$url . PA_ROUTE_HOME_PAGE ?>"><?= $caption ?></a></h1>
    <ul>
      <?php 
        $i = 0;
        $cnt = count($level_1);
        $links_string = NULL;
        foreach ($level_1 as $key=>$value) {
          $i++;
          $link_string = '<a href="'.$value['url'].'">'.$value['caption'].'</a>';
          if( $key == 'join_network') {
            $link_string = '<b>'.$link_string.'</b>';
          } 
          $link_string = ( $cnt == $i ) ? $link_string : $link_string.' | ';
          $link_string = '<li>'.$link_string.'</li>';
          $links_string .= $link_string;
        }
      
        echo $links_string;
      ?>
    </ul>
  </div>
</div>
<?php } ?>
<div id="body_shadow"></div>
<div id="shadow"></div>