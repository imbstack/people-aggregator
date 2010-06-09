<?php

if (!PA::$network_capable) {
?>
<h4><?= __("Multiple networks are disabled - please configure your system for network operation.") ?></h4>
<?
} else {

?>
<form action="" method="post">
  <fieldset class="center_box"> 
    <div class="field_medium"> 
      <h4><?php if( !empty( $featured_network)) {
	echo sprintf(__('The current featured network is: <a href="%s">%s</a>'), 'http://'.$featured_network[0]->address.'.'.PA::$domain_suffix.BASE_URL_REL.PA_ROUTE_HOME_PAGE, $featured_network[0]->name);
      } ?></h4>
    </div>
    <div class="field">
      <?= __("Select a featured network:") ?>
      <select name="feature_network">
        <option value="0"><?= __("Select a network") ?></option>
        <?php $len = count ( $network_links );
          for ( $counter = 0; $counter < $len; $counter++ ) { 
            $selected = '';
            if ( $network_links[$counter]->network_id 
                  == $featured_network[0]->network_id ) { 
              $selected = "selected=\"selected\"";
            } else {
              $selected ='';
            }?>
            <option value="<?php echo $network_links[$counter]->network_id ?>" <?php echo $selected;?>>
            <?php echo stripslashes($network_links[$counter]->name) ?></option>
        <?php } ?>
      </select>
    </div>     
  
  <div class="button_position">
    <input type="submit" value="<?= __("Save") ?>" name="feature_network_save"/>
  </div>
  </fieldset>  
<?php echo $config_navigation_url;?>
</form>
<?
}
?>