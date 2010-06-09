<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
  <div class="module_icon_list" id="list_members">
    <ul class="members">
      <?php
        for ($counter = 0; $counter < count($boards_info); $counter++) {
          $class = (( $counter%2 ) == 0) ? 'class="color"': NULL;
      ?>  
      
      <li <?php echo $class?>>
        &nbsp;<a href="<?= $boards_info[$counter]['url'] ?>"><?= $boards_info[$counter]['title'] ?></a>
        <br />&nbsp;<?= __('type: ') . $boards_info[$counter]['type'] . " " . __('board') ?>
      </li>
      <?php 
        }
      ?>          
    </ul>         
  </div>
