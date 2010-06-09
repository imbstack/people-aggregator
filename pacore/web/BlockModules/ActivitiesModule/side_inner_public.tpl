<?php
  $ajax_call = "javascript: ajax_sort_activities('$block_name', '$ajax_url', 'sort_activities')";
?>        
<div class="mkw_river_of_fans" >
  <?php if(($request_method != 'AJAX') && PA::$login_uid) : ?>
    <select id="sort_activities" name="sort_activities" onchange="<?=$ajax_call?>">
    <?php foreach($options as $key=>$value) : $selected = (($key == $selected_option) ? 'selected="selected"' : NULL) ?>
       <option value="<?= $key ?>" <?= $selected ?> ><?= $value ?></option>
    <?php endforeach; ?>
    </select>
  <?php endif; ?>  
  <div id="list_<?= $block_name ?>">
  <ul>
    <?php if(!empty($list)) { 
            foreach($list as $element ) { 
              $msg = activities_message($element->subject, $element->object,
                                        $element->type, $element->extra);
    ?>        
    <li>
      <?php echo $msg; ?>
    </li>
    <?php
            }
          }
    ?>
  </ul>  
  </div>
</div>