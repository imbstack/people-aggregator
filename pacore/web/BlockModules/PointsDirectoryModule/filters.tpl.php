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
<ul id="filters">
  <?php foreach($categories as $cat) : ?>
    <li class="<?= ($cat == $category) ? 'active' : ''?>"><a href="<?= $url_base . "&category=$cat" . (($user_id) ? "&uid=$user_id" : "") ."&fid=$fid"?>"><?= __(ucfirst($cat)) ?></a></li>
  <?php endforeach; ?>
</ul>
