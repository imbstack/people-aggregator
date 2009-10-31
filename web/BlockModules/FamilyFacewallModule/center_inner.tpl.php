<div class="blog" style="overflow:hidden;">

<?php if ($mod->may_see_details) { ?>
  <div style="padding-left: 18px; clear: both; float: left; width:540px">
  <?php 
  for ($counter = 0; $counter < count($links['users_data']); $counter++) {
  ?>
		<div class="buddyimg">
			<div id="imgcontainer_<?= $link['user_id'] ?>" >
				<a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . urlencode($links['users_data'][$counter]['login_name']) ?>">
					<?php echo uihelper_resize_mk_user_img($links['users_data'][$counter]['picture'], 80, 80, 'alt="PA"') ?>
					</a>
			</div>
			<div>
				<p class="buddytext" style="display: block" align="left">
					<b><?= chop_string($links['users_data'][$counter]['display_name'], 12) ?></b><br />
					<?= $links['users_data'][$counter]['family_status'] ?>
				</p>
			</div>
		</div>
		<?php } ?>
  </div>
<? } else { ?>
	<ul class="members">
  <?php
    for ($counter = 0; $counter < count($links['users_data']); $counter++) {
  ?>  
  <li>
  	<span>
          <b><?= link_to($links['users_data'][$counter]['display_name'],
			 "user_blog", array("login" => urlencode($links['users_data'][$counter]['login_name']))) ?></b>,
			 <?= $links['users_data'][$counter]['family_status'] ?>
        </span>
  	<? } ?>
  </li>
</ul>
<?php } ?>          
</div>