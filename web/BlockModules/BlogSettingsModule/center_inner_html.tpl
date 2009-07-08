<?php global  $current_theme_path; ?>
<h2 class="center_module"><span style="padding-right:5px;"><img alt="collapse" src="<?=$current_theme_path?>/images/arrow_dn.gif" id="BlockSettingModule" border="0" height="11" width="11"/></span><?=__("Blog Settings")?></h2>
<div class="blog" >
  <?= __("You have 3 blog options:") ?>
  <ul>
    <li><?=__("You can contribute to the community blog")?></li>
    <li><?=__("You also can have a personal blog. A personal blog shows up on your page with posts routed to the community blog by default. You can opt for those to only be on your personal blog.")?></li>
    <li><?=__("If you already have a blog, you can display your External Blog here.")?></li>
  </ul>
  <form name="blog_save_settings"  action="save_blog_settings.php" method="post" >
  <div>
    <span><input type="checkbox" name="personal_blog" <? if ($status == BLOG_SETTING_STATUS_ALLDISPLAY || $status == PERSONAL_BLOG_SETTING_STATUS ) echo "checked"  ?> ></span>
    <span><?=__("Add a personal blog")?></span>
  </div>
  <div>
    <span><input type="checkbox" name="external_blog" <? if ($status == BLOG_SETTING_STATUS_ALLDISPLAY || $status == EXTERNAL_BLOG_SETTING_STATUS ) echo "checked"  ?>></span>
    <span><?=__("Display my external blog here")?></span>
  </div>
   <hr />
  <div class="buttonbar">
          <input type='hidden' name='action' value='BlogSetting'>
    <ul><li><a onclick='form_submit();' style="color:#000;cursor:pointer; cursor:hand;" ><?=__("Save")?></a></li>
      <li><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . "/" . $_SESSION['user']['id'] ?>" style="color:#000" ><?=__("View my public page")?></a></li>
      <li><a href="<?= PA::$url , PA_ROUTE_EDIT_PROFILE . "?type=blogs_rss"?>" style="color:#000" ><?=__("Configure external blog")?></a></li>
    </ul>
</div>
</div>
