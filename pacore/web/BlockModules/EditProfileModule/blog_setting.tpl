
<h2 class="center_module"></span><?= __("Blog Settings") ?></h2>
<div class="blog-parent">
<div class="blog">
  <?= __("You have 3 blog options") ?>:
  <ul>
    <li><?= __("You can contribute to the community blog") ?></li>
    <li><?= __("You also can have a personal blog. A personal blog shows up on your page with posts routed to the community blog by default. You can opt for those to only be on your personal blog") ?></li>
    <li><?= __("If you already have a blog, you can display your External Blog on My Page") ?>.</li>
  </ul>
  <form name="blog_save_settings" action="<?php echo PA::$url ?>/save_blog_settings.php?mode=blog_rss" method="post" >
  <div>
    <span><input type="checkbox" name="personal_blog" <? if ($status == BLOG_SETTING_STATUS_ALLDISPLAY || $status == PERSONAL_BLOG_SETTING_STATUS ) echo "checked"  ?> ></span>
    <span><?= __("Add a personal blog") ?></span>
  </div>
  <div>
    <span><input type="checkbox" name="external_blog" <? if ($status == BLOG_SETTING_STATUS_ALLDISPLAY || $status == EXTERNAL_BLOG_SETTING_STATUS ) echo "checked"  ?>></span>
    <span><?= __("Display my external blog here") ?></span>
  </div>
   <hr />
  <div class="buttonbar">
    <input type='hidden' name='action' value='BlogSetting'>
    <ul><li><a href="javascript:form_submit();" style="color:#000; cursor:pointer;" ><?= __("Save") ?></a></li>
      <li><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . "/" . PA::$login_uid ?>" style="color:#000" ><?= __("View my public page") ?></a></li>
    </ul>
  </div>
  </form>
</div>
</div>
