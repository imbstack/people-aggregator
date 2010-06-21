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
<div class="familyandkidsfacewall">

<?php if($mod->may_see_details) {?>
  <?php 
  for($counter = 0; $counter < count($links['users_data']); $counter++) {
        ?>
		<div class="face">
			<div class="picture">
				<a href="<?=PA::$url.PA_ROUTE_USER_PUBLIC.'/'.urlencode($links['users_data'][$counter]['login_name'])?>">
					<?php echo uihelper_resize_mk_user_img($links['users_data'][$counter]['picture'], 80, 80, 'alt="PA"')?>
					</a>
			</div>
			<div>
				<p class="name">
					<b><?=chop_string($links['users_data'][$counter]['display_name'], 12)?></b><br />
					<?=$links['users_data'][$counter]['family_status']?>
				</p>
			</div>
		</div>
		<?php
    }?>
<?
}
else {?>
	<ul class="members">
  <?php
    for($counter = 0;
    $counter < count($links['users_data']);
    $counter++) {
        ?>  
  <li>
  	<span>
          <b><?=link_to($links['users_data'][$counter]['display_name'], "user_blog", array("login" => urlencode($links['users_data'][$counter]['login_name'])))?></b>,
			 <?=$links['users_data'][$counter]['family_status']?>
        </span>
  	<?
    }?>
  </li>
</ul>
<?php
}?>          
</div>