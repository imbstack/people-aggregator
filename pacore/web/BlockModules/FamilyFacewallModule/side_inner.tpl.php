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
<div <?= ($mod->may_see_details) ? 'class="module_icon_list"' : '' ?> id="list_members">
<ul class="memb ers">
  <?php
    for ($counter = 0; $counter < count($links['users_data']); $counter++) {
      $class = (( $counter%2 ) == 0) ? 'class="color"': NULL;
  ?>  
  
  <li <?php echo $class?>>
  	<?php if ($mod->may_see_details) { ?>
    <a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . urlencode($links['users_data'][$counter]['login_name']) ?>">
      <?php echo uihelper_resize_mk_user_img($links['users_data'][$counter]['picture'], 35, 35, 'alt="PA"') ?>
    </a>
        <span>
          <b><?= link_to($links['users_data'][$counter]['display_name'],
			 "user_blog", array("login" => urlencode($links['users_data'][$counter]['login_name']))) ?></b><br/>
			 <?= $links['users_data'][$counter]['family_status'] ?>
        </span>
  	<? } else { ?>
  	<span>
          <b><?= link_to($links['users_data'][$counter]['display_name'],
			 "user_blog", array("login" => urlencode($links['users_data'][$counter]['login_name']))) ?></b>,
			 <?= $links['users_data'][$counter]['family_status'] ?>
        </span>
  	<? } ?>
  </li>
  <?php 
    }
  ?>          
</ul>
</div>