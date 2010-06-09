<?php 
?>
<div class="module_icon_list">
		<ul class="members">
    <?php 
      if(is_array($links) && sizeof($links)>0) {
        $counter = 0;
        for ($i = 0; $i < count($links); $i++) {
          $network = $links[$i];
          $extra = unserialize($network['extra'])  ;
          $network_image_name = $network['inner_logo_image'];
          $class = (( $counter%2 ) == 0) ? ' class="color"': NULL;
          $counter++;
          $img = uihelper_resize_mk_img($network_image_name, 35, 35, PA::$theme_rel."/images/default-network-image.gif");
          $network_url = 'http://'.trim($network['address']).'.'.PA::$domain_suffix.BASE_URL_REL.PA_ROUTE_HOME_PAGE;
    ?>
    <li<?php echo $class;?>>
      <a href="<?=$network_url;?>"><?= $img ?>
      <span>
       <b>
      <?php echo chop_string(stripslashes(strip_tags($network['name'])), 11);?>
      </b><br />
         <?php if ($network['member_count']!='') { ?>
   						<?php echo $network['member_count'].' members'; ?>
        <? } ?>
        </span></a>
      </li>
		  <?
        }  // End of For loop 
      } else { ?>	<li><?= __('No network joined yet.'); ?></li> <? } ?>
    </ul>
</div>
