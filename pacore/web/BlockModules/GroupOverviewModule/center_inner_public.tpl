<?php 
  if (is_array($group_details)&& sizeof($group_details)>0) {
    $group_details['description'] = $group_details['description'];
    $url = PA::$url . PA_ROUTE_GROUP . '/gid='.$group_details['collection_id'];
?>  
<div class="module_overview">
<form>
  <fieldset> 
    <div class="description">
      <?php echo $group_details['description']; ?>
    </div>   
      <?php echo $group_details['tag_entry']; ?>
    
    <?php if ( $group_action['hyper_link'] ) { ?>
    <div id="buttonbar">
      <ul>
        <li><a href="<?php echo $group_action['hyper_link']?>"><?php echo __($group_action['caption'])?></a></li>
      </ul>
    </div>
    <? } ?>  
  </fieldset>
</form>  
</div>
<?php 
  }
  else {
    echo __('Group doesn\'t exist');
  }
 ?>