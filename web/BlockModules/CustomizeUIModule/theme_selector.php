<?php
/** !
<<<<<<< HEAD
* theme_selecter.php is a part of PeopleAggregator.
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @author [original author], [Owen Bell: 3 June 2010]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* HTML for selecting the theme
* @example  [optional]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/
=======
* <filename> is a part of PeopleAggregator.
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @author <creator> , <last modified : date>
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* <description including history>
* @example  <optional>
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @package PeopleAggregator
*/
?>
<?php
>>>>>>> a003e71079b6490b904d0d418a45ee9925ab10e8
include "center_inner_private.tpl";
?>

<div id="theme">
<h1><?=__("Select Theme")?></h1>


<form action="" method="post">       
<ul id="select_theme">
   
  <?php 
    if(count($skins) > 0) {
    foreach($skins as $skin) {
        if($skin['name'] == $selected_theme['name']) {
            $selected = 'checked="checked"';
        }
        else {
            $selected = '';
        }
        ?>
      <li>
        <img src="<?php echo PA::$theme_url;?>/skins/<?php echo $skin['name'].'/'.$skin['preview'];?>" height="150" width ="150" />
        <input type="radio" name="form_data[theme]" value="<?php echo $skin['name'];?>" <?php echo $selected;?> />
        <?php echo $skin['caption'];?>
        <?php
        if(strtolower($skin['headerImage']) == 'no') {
            echo ' (No header)';
        }
        ?>
      </li>
    <?php
    }
}
?>  
</ul>
 <div class="button_position">
  <input type="hidden" name="type" value="theme" />
  <input type="hidden" name="uid" value="<?=$uid?>" />
  <input type="hidden" name="gid" value="<?=$gid?>" />
  <input type="hidden" value='<?php echo $settings_type;?>' name="stype" />
  <input type="hidden" value='' name="action" id="form_action" />
  <input type="submit" name="submit" value="<?=__("Apply Changes")?>"  onclick="javascript: document.getElementById('form_action').value='applyTheme';" />
</div>
</form>
</div>
