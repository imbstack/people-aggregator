<?php
?>
<div class="single_post">
<div class="peopleshowcase">
  <h1><?php echo $contents->title;?></h1>
  <table cellspacing="0">
   <tr valign="top">
   <td class="author" width="85">
    <?php    ?>
    <?php echo '<a href="'. PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id . '">'.uihelper_resize_mk_user_img($picture_name, 80, 80, 'alt=""').'</a>'; ?><br /><a href="<?= PA::$url . PA_ROUTE_USER_PUBLIC . '/' . $user_id ?>"><?php echo wordwrap(chop_string($user_name,40),20)?></a>
     </td>
     <td class="message" width="100%">
    <p>
      <?php echo $contents->body;?>
    </p>   
   <?php
     require "common_permalink_content.tpl";
   ?>
   
</div>
</div>