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
<?php
?>
<div class="forum_header">
  <?php if($message) : ?>
    <div class="<?= $message['class']?>">
      <?= $message['message'] ?>
    </div>
  <?php elseif(@$description) : ?>
    <?= $description ?>
  <?php endif ?> 
</div>