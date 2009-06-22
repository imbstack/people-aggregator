<?php global $current_theme_path,$current_theme_rel_path;?>
<div class="module_icon_list">
  <ul>
  <?php if( !empty($network_data) ) {
    $extra = unserialize($network_data[0]->extra)  ;
    $network_image_name = $extra['basic']['header_image']['name'];
    $img =   uihelper_resize_mk_img($network_image_name, 35, 35
                          , "$current_theme_rel_path/images/default-network-image.gif");
  ?>
  <li><a href="http://<? echo $network_data[0]->address .'.' . PA::$domain_suffix.BASE_URL_REL.PA_ROUTE_HOME_PAGE;?>"><?= $img ?></a>
  <a href="http://<? echo $network_data[0]->address .'.' . PA::$domain_suffix.BASE_URL_REL.PA_ROUTE_HOME_PAGE;?>"  style="overflow:hidden"><?php echo $network_data[0]->name; ?></a></li>
  <?php } else { ?>
    <li><?php echo "No Featured Network"; if ( $_SESSION['user']['id'] == SUPER_USER_ID ) { ?>
    <a href="<?php echo PA::$url .'/network_feature.php';?>">Click Here</a> to set a feature network<?php } ?> </li>
  <?php }?>
  </ul>
</div>